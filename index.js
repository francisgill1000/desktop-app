const { app, BrowserWindow, ipcMain } = require("electron");
const os = require("os");
const { exec, spawn } = require("child_process");

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
      executeCommand("cd src && run_backend.bat"),
      executeCommand("cd src && run_jobs.bat"),
      executeCommand("cd src && run_queue.bat"),
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
let workers = null; // Reference to the queue worker process
const phpPath = `${__dirname}/src/backend/php/php`; // Full path to the PHP executable

function createWindow() {
  mainWindow = new BrowserWindow({ width: 1980, height: 900 });
  setTimeout(() => {
    mainWindow.loadURL(url);
  }, 5000);


  // secondaryWindow = new BrowserWindow({
  //   width: 800,
  //   height: 600,
  //   webPreferences: {
  //     nodeIntegration: true,
  //     contextIsolation: false,
  //   },
  // });

  // secondaryWindow.loadFile("index.html");


  // // Handle starting the worker processes in parallel
  // ipcMain.on("start-worker", (event, command) => {

  //   if (workers) {
  //     event.sender.send("error", "Queue Worker is already running.");
  //     return;
  //   }


  //   // Start the queue worker process
  //   workers = spawn(phpPath, ["artisan", "queue:work"], {
  //     cwd: `${__dirname}/src/backend`, // Set working directory to where the Laravel project exists
  //     shell: true,
  //   });

  //   workers.stdout.on("data", (data) => {
  //     event.sender.send("output", `Queue Worker: ${data.toString()}`);
  //   });

  //   workers.stderr.on("data", (data) => {
  //     event.sender.send("error", `Queue Worker Error: ${data.toString()}`);
  //   });

  //   workers.on("close", (code) => {
  //     event.sender.send(
  //       "exit",
  //       code === 0
  //         ? "Queue Worker exited successfully."
  //         : `Queue Worker exited with code ${code}`
  //     );
  //     workers = null; // Reset queue worker process when the process exits
  //   });
  // });

  // // Handle stopping both worker processes
  // ipcMain.on("stop-worker", (event) => {
  //   let stopped = false;

  //   // Stop queue worker if running
  //   if (workers) {
  //     workers.kill();
  //     workers = null;
  //     event.sender.send("exit", "Queue Worker process stopped.");
  //     stopped = true;
  //   }

  //   if (!stopped) {
  //     event.sender.send("error", "No worker process is running.");
  //   }
  // });
}

// When Electron is ready, start the processes
app.on("ready", startProcesses);

app.on("activate", function () {
  if (mainWindow === null) createWindow();
});
