window.toggleLoader = ((loading) => {

    let loader = document.getElementById('loader');

    if (typeof loading !== 'undefined') {
        if (loading) {
            loader.classList.remove('hide');
        }
        else {
            loader.classList.add('hide');
        }
    }
    else {
        if (loader.classList.contains('hide')) {
            loader.classList.remove('hide');
        }
        else {
            loader.classList.add('hide');
        }
    }


});