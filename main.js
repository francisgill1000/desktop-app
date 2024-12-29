const { app, BrowserWindow, ipcMain } = require("electron");
const os = require("os");
const { exec, spawn } = require("child_process");
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
      executeCommand("cd src && run_jdk.bat"),
      executeCommand("cd src && run_sdk.bat"),
      executeCommand("cd src && run_ip_updater.bat"),
      executeCommand("cd src && run_frontend.bat"),
      setTimeout(() => {
        executeCommand("cd src && run_listener.bat");
      }, 30000),
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
let secondaryWindow;

const phpPath = `${__dirname}/src/backend/php/php`; // Full path to the PHP executable

function createWindow() {
  mainWindow = new BrowserWindow({ width: 1980, height: 900 });
  setTimeout(() => {
    mainWindow.loadURL(url);
  }, 5000);

  secondaryWindow = new BrowserWindow({
    width: 800,
    height: 600,
    webPreferences: {
      nodeIntegration: true,
      contextIsolation: false,
    },
  });

  secondaryWindow.loadFile("index.html");

  const processes = {};

  ipcMain.on("start-worker", (event, command) => {
    // Utility function to spawn a process and handle its events
    const spawnProcess = (phpPath, name, args, cwd) => {
      if (processes[name]) {
        event.sender.send("error", `${name} is already running.`);
        return;
      }

      const process = spawn(phpPath, args, { cwd, shell: true });

      process.stdout.on("data", (data) => {
        event.sender.send("output", `${name}: ${data.toString()}`);
      });

      process.stderr.on("data", (data) => {
        event.sender.send("error", `${name} Error: ${data.toString()}`);
      });

      process.on("close", (code) => {
        event.sender.send(
          "exit",
          code === 0
            ? `${name} exited successfully.`
            : `${name} exited with code ${code}`
        );
        processes[name] = null; // Reset the process when it exits
      });

      processes[name] = process;
    };

    const cwd = `${__dirname}/src/backend`;

    // // Start the server
    spawnProcess(phpPath, "Server", ["artisan", "serve:init"], cwd);

    // Start the queue worker
    spawnProcess(phpPath, "Queue Worker", ["artisan", "queue:work"], cwd);

    // Start the scheduler
    spawnProcess(phpPath, "Scheduler", ["artisan", "schedule:work"], cwd);
  });

  // Cleanup function to terminate all processes
  ipcMain.on("stop-worker", (event) => {
    Object.keys(processes).forEach((name) => {
      if (processes[name]) {
        processes[name].kill();
        processes[name] = null;
        event.sender.send("output", `${name} stopped.`);
      }
    });
  });
}

// When Electron is ready, start the processes
app.on("ready", startProcesses);

app.on("activate", function () {
  if (mainWindow === null) createWindow();
});