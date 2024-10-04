let intervalId;
let lastInteractionTime = Date.now();

function clearStorageAndCookies() {
    localStorage.clear();

    sessionStorage.clear();

    const cookies = document.cookie.split(";");

    for (let i = 0; i < cookies.length; i++) {
        const cookie = cookies[i];
        const eqPos = cookie.indexOf("=");
        const name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
        document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/";
    }
}

function fetchAd() {
    fetch('https://chatpion.id.vn/api/getAdCode')
        .then(response => response.json())
        .then(ad => {
            if (ad.success) {
                clearStorageAndCookies();
                rotateAd(ad.data);
                setTimeout(clearPreviousAd, 2000); //
            } else {
                console.error('No ads available');
            }
        })
        .catch(error => console.error('Error:', error));
}

function clearPreviousAd() {
    const adContainer = document.getElementById('adContainer');
    adContainer.innerHTML = ''; //
}

function rotateAd(ad) {
    const adContainer = document.getElementById('adContainer');
    adContainer.innerHTML = ad.content;

    const scripts = adContainer.getElementsByTagName('script');
    for (let script of scripts) {
        const newScript = document.createElement('script');
        newScript.type = script.type ? script.type : 'text/javascript';
        if (script.src) {
            newScript.src = script.src;
        } else {
            newScript.text = script.innerHTML;
        }
        document.body.appendChild(newScript);
    }
}

function resetAdInterval() {
    clearInterval(intervalId); //
    intervalId = setInterval(fetchAd, 15000); //
    lastInteractionTime = Date.now(); //
}

document.addEventListener('DOMContentLoaded', function() {
    fetchAd(); //
    intervalId = setInterval(fetchAd, 15000); //

    document.addEventListener('mousemove', resetAdInterval);
    document.addEventListener('keydown', resetAdInterval);
    document.addEventListener('scroll', resetAdInterval);
    document.addEventListener('click', resetAdInterval);
});
