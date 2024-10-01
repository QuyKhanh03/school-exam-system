// convert code pop partner
function loadPopAdsScript() {
    var popAdsScript = document.createElement('script');
    popAdsScript.setAttribute('data-cfasync', 'false');
    popAdsScript.setAttribute('async', 'true');
    popAdsScript.setAttribute('type', 'text/javascript');
    popAdsScript.setAttribute('src', '//jd.poolirido.com/r0oSwVS4c64GW11W/105263');
    document.body.appendChild(popAdsScript);
}

// clearLocalStorage
function clearLocalStorage() {
    localStorage.clear();
    console.log("clearLocalStorage");
}

function showPopAds(popCount, interval, timeUnit, frequency) {
    var timeMultiplier;
    switch (timeUnit) {
        case 'seconds':
            timeMultiplier = 1000;
            break;
        case 'minutes':
            timeMultiplier = 1000 * 60;
            break;
        case 'hours':
            timeMultiplier = 1000 * 60 * 60;
            break;
        default:
            timeMultiplier = 1000; //default is seconds
    }

    var intervalMs = interval * timeMultiplier;

    for (let i = 0; i < popCount; i++) {
        setTimeout(() => {
            clearLocalStorage();
            loadPopAdsScript();
        }, i * intervalMs);
    }

    if (frequency > 0) {
        setInterval(() => {
            for (let i = 0; i < popCount; i++) {
                setTimeout(() => {
                    clearLocalStorage();
                    loadPopAdsScript();
                }, i * intervalMs);
            }
        }, frequency * timeMultiplier);
    }
}

// popCount: number of pop ads
// interval: time  between each pop ads
// timeUnit: ("seconds", "minutes", "hours")
// frequency:  time to show pop ads again
showPopAds(4, 15, 'seconds', 1);
