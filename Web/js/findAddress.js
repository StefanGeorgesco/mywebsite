const baseUrl = "https://api-adresse.data.gouv.fr/search/";

const searchParamsObject = {
    'type': 'housenumber',
    'limit': 15,
    'lat': '48.854499', // Notre-Dame de Paris
    'lon': '2.349397'
}

function uriOfObject(obj) {
    return encodeURI('?'+Object.entries(obj)
    .map(x => x[0]+'='+x[1])
    .join('&'));
}

function addressRequest(t) {
    return rxjs.ajax.ajax.getJSON(baseUrl + uriOfObject(
        Object.assign(searchParamsObject, {'q': t})
    ));
}

function elemIndex(elem) {
    return Array.from(elem.parentNode.children).indexOf(elem);
}

function findAddressStart() {

    function displayAdressesList(addressList) {
        if (addressList.length > 0) {
            dropdownList.innerHTML = "";
            let myUl = document.createElement('ol');
            dropdownList.appendChild(myUl);
            addressList.forEach(a => {
                let elem = document.createElement('li');
                elem.appendChild(document.createTextNode(a['label']));
                elem.addEventListener('click', selectAddress);
                myUl.appendChild(elem);
            });
            dropdownList.style.display = "block";
        } else {
            dropdownList.style.display = "none";
            dropdownList.innerHTML = "";
        }
    }

    function autoInput(a) {
        elem('address').value = a['label'];
        elem('housenumber').value = a['housenumber'];
        elem('street').value = a['street'];
        elem('postcode').value = a['postcode'];
        elem('city').value = a['city'];
    }

    function clearInput() {
        elem('address').value = "";
        elem('housenumber').value = "";
        elem('street').value = "";
        elem('postcode').value = "";
        elem('city').value = "";
    }

    function selectAddress(ev) {
        dropdownList.style.display = "none";
        dropdownList.innerHTML = "";
        autoInput(addressList[elemIndex(ev.target)]);
    }

    const addressInput = elem('address');
    const dropdownList = document.createElement('div');
    dropdownList.setAttribute('id', 'dropdownList');
    addressInput.parentNode.insertBefore(dropdownList, addressInput.nextSibling);
    var addressList = [];

    elem('clearButton').addEventListener(
        'click',
        clearInput
    );

    rxjs.fromEvent(addressInput, 'keyup')
    .pipe(
        rxjs.operators.debounceTime(100),
        rxjs.operators.map(e => {
            let str = e.target.value;
            return str === "" ? " " : str;
        }),
        rxjs.operators.distinctUntilChanged(),
        rxjs.operators.switchMap(addressRequest),
        rxjs.operators.pluck('features'),
        rxjs.operators.map(ft => ft.map(e => e['properties'])),
        rxjs.operators.map(pr => pr.filter(e => e['type'] === "housenumber")),
        rxjs.operators.tap(
            al => {
                addressList = al;
                displayAdressesList(al);
            }
        )
    )
    .subscribe();

    rxjs.fromEvent(document, 'keydown')
    .pipe(
        rxjs.operators.pluck('code'),
        rxjs.operators.filter(c => c === 'Escape')
    )
    .subscribe(_ => {dropdownList.style.display = "none";});
}
