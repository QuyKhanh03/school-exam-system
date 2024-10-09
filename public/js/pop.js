
function getParameterByName(name, url = window.location.href) {
    const regex = new RegExp(`[?&]${name}=([^&#]*)`, 'i');
    const results = regex.exec(url);
    return results ? decodeURIComponent(results[1]) : null;
}

const urls = [
    "https://ak.aimukreegee.net/4/7929521",
    "https://suitedeatercrutch.com/r9i22iht?key=d650e254b83e0f36d9c01cc170be806b",
    "https://highmanapts.com/iZncPX84FBeVr/105324"
];

let currentIndex = parseInt(localStorage.getItem('currentIndex')) || 0;
let lastPopupTime = parseInt(localStorage.getItem('lastPopupTime')) || 0;
const popDelay = parseInt(getParameterByName('popDelay')) || 0;
const frequency = parseInt(getParameterByName('frequency')) || 60000; //

function openPopup() {
    const popupUrl = urls[currentIndex];
    window.open(popupUrl, '_blank'); //

    currentIndex = (currentIndex + 1) % urls.length; //
    lastPopupTime = Date.now(); //

    localStorage.setItem('currentIndex', currentIndex);
    localStorage.setItem('lastPopupTime', lastPopupTime);
}

function handleClick(event) {
    const currentTime = Date.now();
    const timeSinceLastPopup = currentTime - lastPopupTime;

    if ((lastPopupTime === 0 || timeSinceLastPopup >= popDelay) && timeSinceLastPopup >= frequency) {
        openPopup();
    }
}

document.addEventListener('click', handleClick);
