var data = {};

let getNewDataForMessage = (event) => {
      let name = event.target.name;
      let value = event.target.value;
      data[name] = value;
};

let roomInput = document.querySelector("#room");
let startTimeInput = document.querySelector("#start-time");
let endTimeInput = document.querySelector("#end-time");

data["room"] = roomInput.value;
data["start_time"]  = startTimeInput.value;
data["end_time"]  = endTimeInput.value;

roomInput.addEventListener("change", getNewDataForMessage);
startTimeInput.addEventListener("change", getNewDataForMessage);
endTimeInput.addEventListener("change", getNewDataForMessage);


let toogleNotificationMessageArea = () =>  {
    let checkbox = document.querySelector("#create-notification-checkbox")
    let textArea = document.querySelector("textarea[name='notification_message']");
    if( checkbox.checked ) {
        let activityName = document.querySelector("input[name=name]").value;
        let {start_time,end_time,room} = data;
        let message = `${activityName} har flyttats till ${start_time} - ${end_time} i ${room}`;
        textArea.innerText = message;
        textArea.disabled = false;
        textArea.focus();
    } else {
        textArea.innerText = "";
        textArea.disabled = true;
    }
};



let checkbox = document.querySelector("#create-notification-checkbox");

checkbox.addEventListener("change", toogleNotificationMessageArea);