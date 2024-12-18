const { ipcRenderer } = require("electron");

// Start Queue Worker
document.getElementById("startWorkers").addEventListener("click", () => {
  ipcRenderer.send("start-worker", "queue:work");
});


// Stop Worker
document.getElementById("stopWorker").addEventListener("click", () => {
  ipcRenderer.send("stop-worker");
});

// Listen for output
ipcRenderer.on("output", (event, data) => {
  const output = document.getElementById("output");
  output.innerHTML += data + "<br>";
});

// Listen for errors
ipcRenderer.on("error", (event, data) => {
  const output = document.getElementById("output");
  output.innerHTML += "ERROR: " + data + "<br>";
});

// Listen for exit messages
ipcRenderer.on("exit", (event, data) => {
  const output = document.getElementById("output");
  output.innerHTML += "EXIT: " + data + "<br>";
});

// Clear content
document.getElementById("clearContent").addEventListener("click", () => {
  const output = document.getElementById("output");
  output.innerHTML = "";
});
