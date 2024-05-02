const { app, BrowserWindow } = require("electron");
const os = require("os");
const { exec } = require("child_process");
const path = require("path");

// Function to execute a command with better error handling
function executeCommand(command, options = {}) {
  return new Promise((resolve, reject) => {
    exec(command, options, (error, stdout, stderr) => {
      if (error) {
        console.error(`Error executing command: ${command}`);
        console.error(stderr || error.message);
        reject(error);
      } else {
        resolve(stdout);
      }
    });
  });
}

async function startProcesses() {
  try {
    // Execute each command asynchronously
    await Promise.all([
      executeCommand("cd src && run_sdk.bat"),
      executeCommand("cd src && run_ip_updater.bat"),
      executeCommand("cd src && run_frontend.bat"),
      executeCommand("cd src && run_backend.bat"),
      executeCommand("cd src && run_jobs.bat"),
      executeCommand("cd src && run_queue.bat"),
      setTimeout(() => {
        executeCommand("cd src && run_listener.bat");
      }, 10000),
      createWindow(),
    ]);
  } catch (error) {
    // Handle errors here
    console.error("Error starting processes:", error);
    // You might want to gracefully handle the error, e.g., show an error dialog
  }
}

const networkInterfaces = os.networkInterfaces();

// Find the IPv4 address of the local machine
let ipv4Address = null;
const port = 3001;

Object.keys(networkInterfaces).forEach((interfaceName) => {
  networkInterfaces[interfaceName].forEach((networkInterface) => {
    // Only consider IPv4 addresses, ignore internal and loopback addresses
    if (networkInterface.family === "IPv4" && !networkInterface.internal) {
      ipv4Address = networkInterface.address;
    }
  });
});

let url = `http://${ipv4Address ?? "localhost"}:${port}`;

let mainWindow;

function createWindow() {
  mainWindow = new BrowserWindow({ width: 1980, height: 900 });
  setTimeout(() => {
    mainWindow.loadURL(url);
  }, 5000);
}

// When Electron is ready, start the processes
app.on("ready", startProcesses);

app.on("activate", function () {
  if (mainWindow === null) createWindow();
});
