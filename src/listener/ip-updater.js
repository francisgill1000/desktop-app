const fs = require("fs")

let path = "../device_sdk/appsettings.json";

const os = require("os");
const networkInterfaces = os.networkInterfaces();

let ipv4Address = null;

Object.keys(networkInterfaces).forEach((interfaceName) => {
  networkInterfaces[interfaceName].forEach((networkInterface) => {
    // Only consider IPv4 addresses, ignore internal and loopback addresses
    if (networkInterface.family === "IPv4" && !networkInterface.internal) {
      ipv4Address = networkInterface.address;
    }
  });
});

// Read the existing JSON file
const jsonData = fs.readFileSync(path);

// Parse the JSON data into a JavaScript object
const data = JSON.parse(jsonData);

// Modify the JavaScript object as needed
data.urls = `http://${ipv4Address}:8080`;
data.Options.LocalIP = ipv4Address;


// Convert the JavaScript object back into JSON format
const updatedJsonData = JSON.stringify(data, null, 2);

// Write the updated JSON data to the file
fs.writeFile(path, updatedJsonData, (err) => {
  if (err) throw err;
  console.log("JSON file has been updated!");
});
