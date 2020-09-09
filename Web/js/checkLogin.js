const login_API_URL = '/API/login-check';

function loginAPICall(login) {
    return rxjs.ajax.ajax.getJSON(
        login_API_URL + encodeURI('?login=' + login)
    );
}

function displayloginInfo(info, login) {
    let error_message = 'Ce login est déjà pris. ';

    if (info['request_success'] &&
        info['login_valid'] &&
        elem('login').value !== login) {
        if (info['login_free'] ||
            elem('login').value.toLowerCase() === login.toLowerCase())
        {
            elem('login').style.backgroundColor = "lightgreen";
            let html = elem('login-errorMessage').innerHTML;
            elem('login-errorMessage').innerHTML = html.replace(
                error_message,
                ''
            );
        } else {
            elem('login').style.backgroundColor = "pink";
            let html = elem('login-errorMessage').innerHTML;
            elem('login-errorMessage').innerHTML = html + error_message;
        }
    } else {
        elem('login').style.backgroundColor = "initial";
        let html = elem('login-errorMessage').innerHTML;
        elem('login-errorMessage').innerHTML = html.replace(
            error_message,
            ''
        );
    }
}

function checkLoginStart() {
    const initial_login = elem('login').value;

    rxjs.merge(
        rxjs.fromEvent(elem('login'), 'keyup').pipe(
            rxjs.operators.debounceTime(100)
        ),
        rxjs.fromEvent(elem('login'), 'change'),
        rxjs.fromEvent(document.getElementsByTagName('form')[0], 'reset')
    )
    .pipe(
        rxjs.operators.map(e => e.target.value),
        rxjs.operators.distinctUntilChanged(),
        rxjs.operators.switchMap(loginAPICall),
        rxjs.operators.tap(info => displayloginInfo(info, initial_login))
      )
      .subscribe();
}
