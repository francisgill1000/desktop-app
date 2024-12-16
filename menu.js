// menu.js
const { Menu } = require('electron');

/**
 * Create and return the application menu
 * @param {BrowserWindow} mainWindow - The main BrowserWindow instance
 */
function createAppMenu(mainWindow) {
  const menuTemplate = [
    {
      label: 'File',
      submenu: [
        {
          label: 'Main Menu',
          click: () => {
            mainWindow.loadFile('index.html'); // Navigate to the Main Menu page
          },
        },
        {
          label: 'Services',
          click: () => {
            mainWindow.loadFile('services.html'); // Navigate to the Services page
          },
        },
        { type: 'separator' },
        {
          label: 'Exit',
          role: 'quit', // Built-in role to quit the app
        },
      ],
    },
  ];

  return Menu.buildFromTemplate(menuTemplate);
}

module.exports = { createAppMenu };
