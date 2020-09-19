const login_API_URL = '/API/login-check';

function loginAPICall(login) {
    return rxjs.ajax.ajax.getJSON(
        login_API_URL + encodeURI('?login=' + login)
    );
}

function displayloginInfo(info, initial_login) {
    const login_elem = elem('login');
    const error_message_elem = elem('login-errorMessage');
    const error_message = 'Ce login est déjà pris. ';

    function setMessage()
    {
        let html = error_message_elem.innerHTML;
        error_message_elem.innerHTML = html + error_message;
    }

    function clearMessage()
    {
        let html = error_message_elem.innerHTML;
        error_message_elem.innerHTML = html.replace(error_message, '');
    }

    clearMessage();

    if (info['login_valid'] &&
        login_elem.value !== initial_login) {
        if (info['login_free'] ||
            login_elem.value.toLowerCase() === initial_login.toLowerCase())
        {
            login_elem.style.backgroundColor = "lightgreen";
            clearMessage();
        } else {
            login_elem.style.backgroundColor = "pink";
            setMessage();
        }
    } else {
        login_elem.style.backgroundColor = "initial";
        clearMessage();
    }
}

function checkLoginStart() {
    const login_elem = elem('login');
    const initial_login = elem('initial_login').value;

    loginAPICall(login_elem.value).subscribe(
        info => displayloginInfo(info, initial_login)
    );

    rxjs.merge(
        rxjs.fromEvent(login_elem, 'keyup').pipe(
            rxjs.operators.debounceTime(100)
        ),
        rxjs.fromEvent(login_elem, 'change'),
        rxjs.fromEvent(document.querySelector('form'), 'reset').pipe(
            rxjs.operators.delay(100)
        )
    )
    .pipe(
        rxjs.operators.map(_ => login_elem.value),
        rxjs.operators.distinctUntilChanged(),
        rxjs.operators.switchMap(loginAPICall),
        rxjs.operators.tap(info => displayloginInfo(info, initial_login))
      )
      .subscribe();
}
