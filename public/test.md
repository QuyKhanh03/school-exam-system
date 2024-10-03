galaksion: 
 <div class="galaksion"><script data-cfasync="false" async type="text/javascript" src="//jd.poolirido.com/r0oSwVS4c64GW11W/105263"></script></div>
adsterra:
<div class="adsterra"><script type='text/javascript' src='//suitedeatercrutch.com/4e/15/48/4e1548277d60fd7d248054c71ddb644b.js'></script></div>
propellerads:
<div class="propellerads"><script type="text/javascript" src="//loghutouft.net/5/7544776" async data-cfasync="false"></script></div>

Adcash:

<script id="aclib" type="text/javascript" src="//acscdn.com/script/aclib.js"></script>
<script type="text/javascript">
    aclib.runPop({
        zoneId: '8409910',
    });
</script>



<script>
    let popUpDisplayed = false; // Trạng thái để kiểm soát hiển thị pop-up

    function loadPartnerScript() {
        if (popUpDisplayed) {
            console.log("Pop-up is already displayed, skipping this cycle.");
            return; // Nếu pop-up đã hiển thị, không tải thêm pop-up
        }

        console.log("Loading PropellerAds script...");

        // Xóa script cũ nếu đã tồn tại
        const existingScript = document.querySelector('.propellerads script');
        if (existingScript) {
            existingScript.remove();
        }

        const script = document.createElement('script');
        script.type = 'text/javascript';
        script.src = "//loghutouft.net/5/7544776";
        script.async = true;
        script.setAttribute('data-cfasync', 'false');

        document.querySelector('.propellerads').appendChild(script);

        popUpDisplayed = true; // Đánh dấu rằng pop-up đã được hiển thị
        console.log("PropellerAds script loaded.");

        // Sau 10 giây, cho phép hiển thị pop-up tiếp theo
        setTimeout(() => {
            popUpDisplayed = false; // Cho phép hiển thị pop-up mới sau 10 giây
            console.log("Pop-up display time expired, ready for next.");
        }, 10000); // 10 giây
    }

    function clearCookiesForMyDomain() {
        console.log("Clearing cookies for my domain...");
        const cookies = document.cookie.split(";");

        for (let i = 0; i < cookies.length; i++) {
            const cookie = cookies[i];
            const eqPos = cookie.indexOf("=");
            const name = eqPos > -1 ? cookie.substr(0, eqPos).trim() : cookie.trim();
            const domain = window.location.hostname; // Get current domain

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

    // Hiển thị pop-up ngay khi trang được tải
    loadPartnerScript();

    // Sau 10 giây, kiểm tra lại và hiển thị pop-up nếu cần
    setInterval(function() {
        console.log("Starting the process to clear cookies for my domain, localStorage, and sessionStorage...");
        clearCookiesForMyDomain();
        clearStorage();
        console.log("Process complete. Now reloading partner script...");

        loadPartnerScript(); // Gọi lại để hiển thị pop-up nếu chưa có pop-up nào hiển thị

    }, 10000); // 10 giây
</script>
