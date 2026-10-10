let urlParams = new URL(window.location).searchParams;
let beingDisplayedOnMonitor = urlParams.get("m") == "1";
if(beingDisplayedOnMonitor) {
  refresh_handler = function(e) {
    for(let container of document.querySelectorAll(".activity-container")) {
      const overflowedColumns = new Set();
      for(let element of container.querySelectorAll("section.activity")) {
        const column = window.getComputedStyle(element).gridColumnStart;
        if(overflowedColumns.has(column)) {
          element.style.display = "none";
          continue;
        }

        var boundingClientRect = element.getBoundingClientRect();

        const top = boundingClientRect.top;
        const bottom = boundingClientRect.bottom;
        const elementHeight = boundingClientRect.height;
        const windowHeight = window.innerHeight;
        if(windowHeight > top + elementHeight / 3 && windowHeight < bottom) {
          const overlap = bottom - windowHeight;
          const newHeight = elementHeight - overlap;
          let description = element.querySelector("p");
          if(description) {
            description.style.maxHeight = newHeight / 3;
            description.classList.add("break");
          }
          overflowedColumns.add(column);
        } else if( windowHeight < bottom ) {
          element.style.display = "none";
          overflowedColumns.add(column);
        }
      }
    }
  };

  window.addEventListener('scroll', refresh_handler);
  window.addEventListener('load', refresh_handler);
  window.addEventListener('resize', refresh_handler);
}
