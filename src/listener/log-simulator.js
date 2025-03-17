const WebSocket = require("ws");
const fs = require("fs");

// Replace this with your WebSocket server endpoint
const SOCKET_ENDPOINT = "ws://192.168.2.12:8080/WebSocket";

// Create a WebSocket connection to send test data
const socket = new WebSocket(SOCKET_ENDPOINT);

socket.onopen = () => {
    console.log(`Connected to ${SOCKET_ENDPOINT}`);
    sendFakeEntries();
};

socket.onerror = (error) => {
    console.error("WebSocket error:", error.message);
};

socket.onclose = (event) => {
    console.log(`WebSocket connection closed with code ${event.code}`);
};

function sendFakeEntries() {
    const fakeEntry = `${3},${`OX-8662022010295`},${`2025-01-16 12:02:12`},${`41415`},${`Allowed`},${`Face`},${`---`}`;
    console.log("🚀 ~ setInterval ~ fakeEntry:", fakeEntry)
    // socket.send(JSON.stringify(fakeEntry));
}
