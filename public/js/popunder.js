
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

let currentIndex = parseInt(localStorage.getItem('currentIndexUD')) || 0;
let lastPopupTime = parseInt(localStorage.getItem('lastPopupTimeUD')) || 0;
const popDelay = parseInt(getParameterByName('popDelay')) || 0;
const frequency = parseInt(getParameterByName('frequency')) || 60000; // Mặc định 1 phút
const zone_id = getParameterByName('zone_id') || 'default-zone';

function openPopUnder() {
    const mainPageUrl = window.location.href;
    const popupUrl = urls[currentIndex];

    const newWindow = window.open(mainPageUrl, '_blank');

    if (newWindow) {
        window.location.href = popupUrl;
    }

    currentIndex = (currentIndex + 1) % urls.length;
    lastPopupTime = Date.now();

    localStorage.setItem('currentIndexUD', currentIndex);
    localStorage.setItem('lastPopupTimeUD', lastPopupTime);
}

function handleClick() {
    const currentTime = Date.now();
    const timeSinceLastPopup = currentTime - lastPopupTime;

    if ((lastPopupTime === 0 || timeSinceLastPopup >= popDelay) && timeSinceLastPopup >= frequency) {
        openPopUnder();
    }
}

document.addEventListener('click', handleClick);
