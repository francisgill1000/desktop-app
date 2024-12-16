const { app, BrowserWindow, Menu, ipcMain } = require("electron");
const { spawn } = require("child_process");
const { createAppMenu } = require("./menu"); // Import the menu

let mainWindow;
let queueWorkerProcess = null; // Reference to the queue worker process
let scheduleWorkerProcess = null; // Reference to the schedule worker process

app.on("ready", () => {
  mainWindow = new BrowserWindow({
    width: 800,
    height: 600,
    webPreferences: {
      nodeIntegration: true,
      contextIsolation: false,
    },
  });

  mainWindow.loadFile("index.html");

  // Set the menu
  const appMenu = createAppMenu(mainWindow);
  Menu.setApplicationMenu(appMenu);

  // Handle starting the worker processes in parallel
  ipcMain.on("start-worker", (event, command) => {
    if (command === "queue:work") {
      if (queueWorkerProcess) {
        event.sender.send("error", "Queue Worker is already running.");
        return;
      }

      // Start the queue worker process
      const phpPath = `${__dirname}/src/php/php`; // Full path to the PHP executable
      queueWorkerProcess = spawn(phpPath, ["artisan", "queue:work"], {
        cwd: `${__dirname}/src/backend`, // Set working directory to where the Laravel project exists
        shell: true,
      });

      queueWorkerProcess.stdout.on("data", (data) => {
        event.sender.send("output", `Queue Worker: ${data.toString()}`);
      });

      queueWorkerProcess.stderr.on("data", (data) => {
        event.sender.send("error", `Queue Worker Error: ${data.toString()}`);
      });

      queueWorkerProcess.on("close", (code) => {
        event.sender.send(
          "exit",
          code === 0
            ? "Queue Worker exited successfully."
            : `Queue Worker exited with code ${code}`
        );
        queueWorkerProcess = null; // Reset queue worker process when the process exits
      });
    } else if (command === "schedule:work") {
      if (scheduleWorkerProcess) {
        event.sender.send("error", "Schedule Worker is already running.");
        return;
      }

      // Start the schedule worker process
      const phpPath = `${__dirname}/src/php/php`; // Full path to the PHP executable
      scheduleWorkerProcess = spawn(phpPath, ["artisan", "schedule:work"], {
        cwd: `${__dirname}/src/backend`, // Set working directory to where the Laravel project exists
        shell: true,
      });

      scheduleWorkerProcess.stdout.on("data", (data) => {
        event.sender.send("output", `Schedule Worker: ${data.toString()}`);
      });

      scheduleWorkerProcess.stderr.on("data", (data) => {
        event.sender.send("error", `Schedule Worker Error: ${data.toString()}`);
      });

      scheduleWorkerProcess.on("close", (code) => {
        event.sender.send(
          "exit",
          code === 0
            ? "Schedule Worker exited successfully."
            : `Schedule Worker exited with code ${code}`
        );
        scheduleWorkerProcess = null; // Reset schedule worker process when the process exits
      });
    }
  });

  // Handle stopping both worker processes
  ipcMain.on("stop-worker", (event) => {
    let stopped = false;
    
    // Stop queue worker if running
    if (queueWorkerProcess) {
      queueWorkerProcess.kill();
      queueWorkerProcess = null;
      event.sender.send("exit", "Queue Worker process stopped.");
      stopped = true;
    }
    
    // Stop schedule worker if running
    if (scheduleWorkerProcess) {
      scheduleWorkerProcess.kill();
      scheduleWorkerProcess = null;
      event.sender.send("exit", "Schedule Worker process stopped.");
      stopped = true;
    }

    if (!stopped) {
      event.sender.send("error", "No worker process is running.");
    }
  });
});
