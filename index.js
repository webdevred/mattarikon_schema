setTimeout(function() { window.location.reload(); }, 60000);

let urlParams = new URL(window.location).searchParams;
let beingDisplayedOnMonitor = urlParams.get("m") == "1";
if(beingDisplayedOnMonitor) {
    refresh_handler = function(e) {
        for(let element of document.querySelectorAll("section.activity")) {
            var boundingClientRect = element.getBoundingClientRect();

            const top = boundingClientRect.top;
            const bottom = boundingClientRect.bottom;
            const elementHeight = boundingClientRect.height;
            const windowHeight = window.innerHeight;
            if(windowHeight > top + elementHeight / 3 && windowHeight < bottom) {
                const overlap = bottom - windowHeight;
                const newHeight = elementHeight - overlap;
                let description = element.querySelector("p");
                description.style.maxHeight = newHeight / 3;
                description.classList.add("break");
            } else if( windowHeight < bottom ) {
                element.style.display = "none";
            }
        }
    };

    window.addEventListener('scroll', refresh_handler);
    window.addEventListener('load', refresh_handler);
    window.addEventListener('resize', refresh_handler);
}
