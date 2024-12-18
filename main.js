const { app, BrowserWindow, Menu, ipcMain } = require("electron");
const { spawn } = require("child_process");
const { createAppMenu } = require("./menu"); // Import the menu

let mainWindow;
let workers = null; // Reference to the queue worker process
const phpPath = `${__dirname}/src/backend/php/php`; // Full path to the PHP executable


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

    if (workers) {
      event.sender.send("error", "Queue Worker is already running.");
      return;
    }


    // Start the queue worker process
    workers = spawn(phpPath, ["artisan", "queue:work"], {
      cwd: `${__dirname}/src/backend`, // Set working directory to where the Laravel project exists
      shell: true,
    });

    workers.stdout.on("data", (data) => {
      event.sender.send("output", `Queue Worker: ${data.toString()}`);
    });

    workers.stderr.on("data", (data) => {
      event.sender.send("error", `Queue Worker Error: ${data.toString()}`);
    });

    workers.on("close", (code) => {
      event.sender.send(
        "exit",
        code === 0
          ? "Queue Worker exited successfully."
          : `Queue Worker exited with code ${code}`
      );
      workers = null; // Reset queue worker process when the process exits
    });
  });

  // Handle stopping both worker processes
  ipcMain.on("stop-worker", (event) => {
    let stopped = false;

    // Stop queue worker if running
    if (workers) {
      workers.kill();
      workers = null;
      event.sender.send("exit", "Queue Worker process stopped.");
      stopped = true;
    }

    if (!stopped) {
      event.sender.send("error", "No worker process is running.");
    }
  });
});
