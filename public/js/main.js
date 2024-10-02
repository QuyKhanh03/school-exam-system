let popUpDisplayed = false;
let inactivityTimeout;
let popUpTimeout;

function loadPartnerScript(partner) {
    if (popUpDisplayed) {
        console.log("Pop-up is already displayed, preventing multiple pop-ups.");
        return;
    }

    console.log(`Loading ${partner} pop-up script...`);

    const existingScript = document.querySelector(`.${partner} script`);
    if (existingScript) {
        existingScript.remove();
    }

    let script = document.createElement('script');
    script.type = 'text/javascript';
    script.async = true;

    switch (partner) {
        case 'galaksion':
            script.src = "//jd.poolirido.com/r0oSwVS4c64GW11W/105263";
            document.querySelector('.galaksion').appendChild(script);
            break;
        case 'adsterra':
            script.src = "//suitedeatercrutch.com/4e/15/48/4e1548277d60fd7d248054c71ddb644b.js";
            document.querySelector('.adsterra').appendChild(script);
            break;
        case 'propellerads':
            script.src = "//loghutouft.net/5/7544776";
            document.querySelector('.propellerads').appendChild(script);
            break;
    }

    popUpDisplayed = true;
    localStorage.setItem('lastPopUp', partner);

    setTimeout(() => {
        popUpDisplayed = false;
        console.log(`${partner} pop-up display time expired, ready for next.`);
    }, 60000); // Thời gian chờ 60 giây

    console.log(`${partner} pop-up script loaded.`);
}

function clearCookiesForMyDomain() {
    console.log("Clearing cookies for my domain...");
    const cookies = document.cookie.split(";");

    for (let i = 0; i < cookies.length; i++) {
        const cookie = cookies[i];
        const eqPos = cookie.indexOf("=");
        const name = eqPos > -1 ? cookie.substr(0, eqPos).trim() : cookie.trim();
        const domain = window.location.hostname;

        document.cookie = `${name}=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/;domain=${domain}`;
        console.log(`Cookie cleared: ${name} for domain: ${domain}`);
    }
    console.log("All cookies for my domain cleared.");
}

function clearStorage() {
    console.log("Clearing localStorage and sessionStorage...");
    localStorage.clear();
    sessionStorage.clear();
    console.log("LocalStorage and SessionStorage cleared.");
}

function getRandomPartner(excludePartner) {
    const partners = ['galaksion', 'adsterra', 'propellerads'];
    const filteredPartners = partners.filter(partner => partner !== excludePartner);

    return filteredPartners[Math.floor(Math.random() * filteredPartners.length)];
}

function resetInactivityTimeout() {
    clearTimeout(inactivityTimeout);
    inactivityTimeout = setTimeout(() => {
        console.log("Người dùng không tương tác trong 3 phút. Dừng hiển thị pop-up.");
        popUpDisplayed = false; //
    }, 180000); // 180000ms = 3
}

function startAdRotation() {
    let lastPopUp = localStorage.getItem('lastPopUp');
    const newPartner = getRandomPartner(lastPopUp);
    loadPartnerScript(newPartner);

    popUpTimeout = setInterval(function() {
        if (!popUpDisplayed) {
            console.log("Starting the process to clear cookies for my domain, localStorage, and sessionStorage...");
            clearCookiesForMyDomain();
            clearStorage();
            console.log("Process complete. Now loading a new partner pop-up...");

            lastPopUp = localStorage.getItem('lastPopUp');
            const nextPartner = getRandomPartner(lastPopUp);
            loadPartnerScript(nextPartner);
        }
    }, 30000); //
}

//
window.addEventListener('click', resetInactivityTimeout);
window.addEventListener('scroll', resetInactivityTimeout);
window.addEventListener('mousemove', resetInactivityTimeout);
window.addEventListener('keydown', resetInactivityTimeout);

//
startAdRotation();
resetInactivityTimeout();
