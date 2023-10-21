    setTimeout(function() { window.location.reload(); }, 60000);
    
    let urlParams = new URL(window.location).searchParams;
    let beingDisplayedOnMonitor = urlParams.get("m") == "1";
    if(beingDisplayedOnMonitor) {
    refresh_handler = function(e) {
        for(let element of document.querySelectorAll("section.activity")) {
            var boundingClientRect = element.getBoundingClientRect();
            if ( window.innerHeight < boundingClientRect.bottom ) {
                console.log(boundingClientRect);
                element.style.display = "none";
            }
        }
   };

   window.addEventListener('scroll', refresh_handler);
   window.addEventListener('load', refresh_handler);
   window.addEventListener('resize', refresh_handler);
}