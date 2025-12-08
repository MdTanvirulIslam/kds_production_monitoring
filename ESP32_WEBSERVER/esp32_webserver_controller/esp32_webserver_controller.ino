/*
 * ============================================
 * ESP32 + WS2812B - With Web Server Interface
 * Factory Table Light Indicator System
 * ============================================
 *
 * Features:
 * - Polls Laravel server for commands
 * - Local Web Server interface for direct control
 * - Push button alert
 * - Buzzer feedback
 *
 * Access web interface: http://[ESP32_IP_ADDRESS]
 *
 * ============================================
 */

#include <WiFi.h>
#include <WebServer.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <Adafruit_NeoPixel.h>

// ============================================
// ⚠️ CONFIGURATION - CHANGE THESE VALUES ⚠️
// ============================================

// WiFi Settings
const char* ssid = "YOUR SSID";
const char* password = "YOUR WIFI PASSWORD";

// Server Settings - YOUR CPANEL DOMAIN
const char* serverHost = "http://kds.differentcoder.website";
const char* tableNumber = "T005";
const char* deviceId = "ESP32_005";

// Polling interval (milliseconds)
const int POLL_INTERVAL = 3000;  // Poll every 3 seconds

// LED Strip Settings
#define LED_PIN     5
#define NUM_LEDS    10
#define BRIGHTNESS  150

// Button & Buzzer
#define BUTTON_PIN  4
#define BUZZER_PIN  18

// ============================================
// GLOBAL VARIABLES
// ============================================

Adafruit_NeoPixel strip(NUM_LEDS, LED_PIN, NEO_GRB + NEO_KHZ800);

// Web Server on port 80
WebServer server(80);

String currentColor = "off";
unsigned long lastPollTime = 0;
unsigned long lastStatusTime = 0;
unsigned long startTime = 0;

// Button debounce
bool buttonPressed = false;
bool lastButtonState = HIGH;
unsigned long lastDebounceTime = 0;

// Stats
int alertCount = 0;
int commandCount = 0;
String lastCommand = "None";
String lastCommandTime = "Never";

// ============================================
// HTML WEB INTERFACE
// ============================================

String getWebPage() {
    String html = R"rawliteral(
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESP32 Control Panel - )rawliteral" + String(tableNumber) + R"rawliteral(</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            padding: 20px;
            color: #fff;
        }
        .container { max-width: 600px; margin: 0 auto; }

        .header {
            text-align: center;
            padding: 20px;
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            margin-bottom: 20px;
        }
        .header h1 { font-size: 24px; margin-bottom: 5px; }
        .header .table-id {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 8px 20px;
            border-radius: 20px;
            display: inline-block;
            font-size: 18px;
            font-weight: bold;
        }

        .status-card {
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .status-card h3 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 16px;
        }
        .status-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .status-item {
            background: rgba(0,0,0,0.2);
            padding: 12px;
            border-radius: 10px;
        }
        .status-item .label { font-size: 12px; color: #888; }
        .status-item .value { font-size: 16px; font-weight: bold; margin-top: 5px; }
        .status-item .value.online { color: #2ecc71; }
        .status-item .value.offline { color: #e74c3c; }

        .led-indicator {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 20px auto;
            border: 4px solid rgba(255,255,255,0.3);
            transition: all 0.3s ease;
        }
        .led-off { background: #333; box-shadow: none; }
        .led-red { background: #e74c3c; box-shadow: 0 0 30px #e74c3c; }
        .led-green { background: #2ecc71; box-shadow: 0 0 30px #2ecc71; }
        .led-blue { background: #3498db; box-shadow: 0 0 30px #3498db; }
        .led-yellow { background: #f1c40f; box-shadow: 0 0 30px #f1c40f; }
        .led-white { background: #fff; box-shadow: 0 0 30px #fff; }

        .control-section {
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .control-section h3 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .color-buttons {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        .color-btn {
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            color: #fff;
        }
        .color-btn:hover { transform: scale(1.05); }
        .color-btn:active { transform: scale(0.95); }
        .btn-red { background: #e74c3c; }
        .btn-green { background: #2ecc71; }
        .btn-blue { background: #3498db; }
        .btn-yellow { background: #f1c40f; color: #333; }
        .btn-white { background: #ecf0f1; color: #333; }
        .btn-off { background: #333; border: 2px solid #555; }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 15px;
        }
        .action-btn {
            padding: 12px;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-alert { background: #e74c3c; color: #fff; }
        .btn-beep { background: #9b59b6; color: #fff; }
        .btn-flash { background: #e67e22; color: #fff; }
        .btn-refresh { background: #1abc9c; color: #fff; }

        .info-section {
            background: rgba(255,255,255,0.05);
            border-radius: 10px;
            padding: 15px;
            font-size: 12px;
            color: #888;
        }
        .info-section p { margin: 5px 0; }

        .footer {
            text-align: center;
            padding: 15px;
            color: #666;
            font-size: 12px;
        }

        /* Auto-refresh indicator */
        .refresh-indicator {
            position: fixed;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.5);
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="refresh-indicator">Auto-refresh: 5s</div>

    <div class="container">
        <div class="header">
            <h1>🏭 ESP32 Control Panel</h1>
            <div class="table-id">)rawliteral" + String(tableNumber) + R"rawliteral(</div>
        </div>

        <!-- Current Status -->
        <div class="status-card">
            <h3>📊 Current Status</h3>
            <div class="led-indicator led-)rawliteral" + currentColor + R"rawliteral("></div>
            <p style="text-align:center; margin-bottom:15px;">Current Color: <strong style="text-transform:uppercase;">)rawliteral" + currentColor + R"rawliteral(</strong></p>

            <div class="status-grid">
                <div class="status-item">
                    <div class="label">WiFi Status</div>
                    <div class="value )rawliteral" + String(WiFi.status() == WL_CONNECTED ? "online" : "offline") + R"rawliteral(">)rawliteral" + String(WiFi.status() == WL_CONNECTED ? "● Connected" : "● Disconnected") + R"rawliteral(</div>
                </div>
                <div class="status-item">
                    <div class="label">Signal Strength</div>
                    <div class="value">)rawliteral" + String(WiFi.RSSI()) + R"rawliteral( dBm</div>
                </div>
                <div class="status-item">
                    <div class="label">IP Address</div>
                    <div class="value">)rawliteral" + WiFi.localIP().toString() + R"rawliteral(</div>
                </div>
                <div class="status-item">
                    <div class="label">Uptime</div>
                    <div class="value">)rawliteral" + getUptime() + R"rawliteral(</div>
                </div>
            </div>
        </div>

        <!-- LED Control -->
        <div class="control-section">
            <h3>💡 LED Control</h3>
            <div class="color-buttons">
                <button class="color-btn btn-red" onclick="setColor('red')">🔴 RED</button>
                <button class="color-btn btn-green" onclick="setColor('green')">🟢 GREEN</button>
                <button class="color-btn btn-blue" onclick="setColor('blue')">🔵 BLUE</button>
                <button class="color-btn btn-yellow" onclick="setColor('yellow')">🟡 YELLOW</button>
                <button class="color-btn btn-white" onclick="setColor('white')">⚪ WHITE</button>
                <button class="color-btn btn-off" onclick="setColor('off')">⚫ OFF</button>
            </div>

            <div class="action-buttons">
                <button class="action-btn btn-alert" onclick="sendAlert()">🚨 Send Alert</button>
                <button class="action-btn btn-beep" onclick="beep()">🔔 Beep</button>
                <button class="action-btn btn-flash" onclick="flash()">⚡ Flash</button>
                <button class="action-btn btn-refresh" onclick="location.reload()">🔄 Refresh</button>
            </div>
        </div>

        <!-- Stats -->
        <div class="status-card">
            <h3>📈 Statistics</h3>
            <div class="status-grid">
                <div class="status-item">
                    <div class="label">Alerts Sent</div>
                    <div class="value">)rawliteral" + String(alertCount) + R"rawliteral(</div>
                </div>
                <div class="status-item">
                    <div class="label">Commands Received</div>
                    <div class="value">)rawliteral" + String(commandCount) + R"rawliteral(</div>
                </div>
                <div class="status-item">
                    <div class="label">Last Command</div>
                    <div class="value">)rawliteral" + lastCommand + R"rawliteral(</div>
                </div>
                <div class="status-item">
                    <div class="label">Device ID</div>
                    <div class="value">)rawliteral" + String(deviceId) + R"rawliteral(</div>
                </div>
            </div>
        </div>

        <!-- Device Info -->
        <div class="info-section">
            <p><strong>Server:</strong> )rawliteral" + String(serverHost) + R"rawliteral(</p>
            <p><strong>Poll Interval:</strong> )rawliteral" + String(POLL_INTERVAL/1000) + R"rawliteral( seconds</p>
            <p><strong>LEDs:</strong> )rawliteral" + String(NUM_LEDS) + R"rawliteral( x WS2812B</p>
        </div>

        <div class="footer">
            Factory Management System - ESP32 Controller v2.0
        </div>
    </div>

    <script>
        // Auto refresh page every 5 seconds
        setTimeout(function(){ location.reload(); }, 5000);

        function setColor(color) {
            fetch('/set?color=' + color)
                .then(response => response.text())
                .then(data => {
                    console.log('Color set:', color);
                    location.reload();
                })
                .catch(error => console.error('Error:', error));
        }

        function sendAlert() {
            fetch('/alert')
                .then(response => response.text())
                .then(data => {
                    console.log('Alert sent');
                    location.reload();
                });
        }

        function beep() {
            fetch('/beep')
                .then(response => response.text())
                .then(data => console.log('Beep'));
        }

        function flash() {
            fetch('/flash')
                .then(response => response.text())
                .then(data => {
                    console.log('Flash');
                    location.reload();
                });
        }
    </script>
</body>
</html>
)rawliteral";

    return html;
}

// Get uptime string
String getUptime() {
    unsigned long seconds = (millis() - startTime) / 1000;
    unsigned long minutes = seconds / 60;
    unsigned long hours = minutes / 60;

    if (hours > 0) {
        return String(hours) + "h " + String(minutes % 60) + "m";
    } else if (minutes > 0) {
        return String(minutes) + "m " + String(seconds % 60) + "s";
    } else {
        return String(seconds) + "s";
    }
}

// ============================================
// WEB SERVER HANDLERS
// ============================================

void handleRoot() {
    server.send(200, "text/html", getWebPage());
}

void handleSetColor() {
    if (server.hasArg("color")) {
        String color = server.arg("color");
        setColor(color);
        lastCommand = "Web: " + color;
        commandCount++;
        server.send(200, "text/plain", "Color set to: " + color);
    } else {
        server.send(400, "text/plain", "Missing color parameter");
    }
}

void handleAlert() {
    sendAlert("web_alert");
    server.send(200, "text/plain", "Alert sent!");
}

void handleBeep() {
    beepBuzzer();
    server.send(200, "text/plain", "Beep!");
}

void handleFlash() {
    flashAlert();
    setColor(currentColor);  // Restore color after flash
    server.send(200, "text/plain", "Flash!");
}

void handleStatus() {
    StaticJsonDocument<512> doc;
    doc["table"] = tableNumber;
    doc["device_id"] = deviceId;
    doc["color"] = currentColor;
    doc["ip"] = WiFi.localIP().toString();
    doc["rssi"] = WiFi.RSSI();
    doc["uptime"] = millis() - startTime;
    doc["alerts"] = alertCount;
    doc["commands"] = commandCount;

    String response;
    serializeJson(doc, response);

    server.send(200, "application/json", response);
}

void handleNotFound() {
    server.send(404, "text/plain", "Not Found");
}

// ============================================
// LED CONTROL
// ============================================

void setAllLEDs(int r, int g, int b) {
    for (int i = 0; i < NUM_LEDS; i++) {
        strip.setPixelColor(i, strip.Color(r, g, b));
    }
    strip.show();
}

void setColor(String color) {
    currentColor = color;

    if (color == "red") {
        setAllLEDs(255, 0, 0);
    } else if (color == "green") {
        setAllLEDs(0, 255, 0);
    } else if (color == "blue") {
        setAllLEDs(0, 0, 255);
    } else if (color == "yellow") {
        setAllLEDs(255, 255, 0);
    } else if (color == "white") {
        setAllLEDs(255, 255, 255);
    } else {
        setAllLEDs(0, 0, 0);
    }

    Serial.println("LED set to: " + color);
}

void flashAlert() {
    for (int i = 0; i < 3; i++) {
        setAllLEDs(255, 0, 0);
        delay(200);
        setAllLEDs(0, 0, 0);
        delay(200);
    }
}

void beepBuzzer() {
    for (int i = 0; i < 3; i++) {
        digitalWrite(BUZZER_PIN, HIGH);
        delay(100);
        digitalWrite(BUZZER_PIN, LOW);
        delay(100);
    }
}

// ============================================
// SERVER COMMUNICATION (Laravel)
// ============================================

void pollServer() {
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("WiFi not connected, skipping poll");
        return;
    }

    HTTPClient http;

    String url = String(serverHost) + "/api/esp32/poll?table=" + tableNumber + "&device_id=" + deviceId;

    Serial.println("Polling: " + url);

    http.begin(url);
    http.setTimeout(5000);

    int httpCode = http.GET();

    if (httpCode == 200) {
        String response = http.getString();
        Serial.println("Response: " + response);

        StaticJsonDocument<512> doc;
        DeserializationError error = deserializeJson(doc, response);

        if (!error) {
            bool success = doc["success"] | false;

            if (success) {
                if (!doc["command"].isNull()) {
                    String cmdColor = doc["command"]["color"] | "off";
                    bool cmdBlink = doc["command"]["blink"] | false;

                    Serial.println("📥 Command received: " + cmdColor);

                    setColor(cmdColor);
                    lastCommand = "Server: " + cmdColor;
                    commandCount++;

                    // Beep on command
                    digitalWrite(BUZZER_PIN, HIGH);
                    delay(50);
                    digitalWrite(BUZZER_PIN, LOW);
                }

                String serverColor = doc["current_color"] | "off";
                if (doc["command"].isNull() && serverColor != currentColor) {
                    Serial.println("Syncing color to: " + serverColor);
                    setColor(serverColor);
                }
            }
        } else {
            Serial.println("JSON parse error: " + String(error.c_str()));
        }
    } else {
        Serial.println("Poll failed, HTTP code: " + String(httpCode));
    }

    http.end();
}

void sendStatus() {
    if (WiFi.status() != WL_CONNECTED) return;

    HTTPClient http;

    String url = String(serverHost) + "/api/esp32/status";

    http.begin(url);
    http.addHeader("Content-Type", "application/json");
    http.setTimeout(5000);

    StaticJsonDocument<256> doc;
    doc["table_number"] = tableNumber;
    doc["device_id"] = deviceId;
    doc["current_color"] = currentColor;
    doc["ip_address"] = WiFi.localIP().toString();
    doc["rssi"] = WiFi.RSSI();

    String jsonData;
    serializeJson(doc, jsonData);

    int httpCode = http.POST(jsonData);

    if (httpCode == 200) {
        Serial.println("Status sent OK");
    } else {
        Serial.println("Status send failed: " + String(httpCode));
    }

    http.end();
}

void sendAlert(String alertType) {
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("WiFi not connected, cannot send alert");
        return;
    }

    HTTPClient http;

    String url = String(serverHost) + "/api/esp32/alert";

    http.begin(url);
    http.addHeader("Content-Type", "application/json");
    http.setTimeout(5000);

    StaticJsonDocument<256> doc;
    doc["table_number"] = tableNumber;
    doc["device_id"] = deviceId;
    doc["alert_type"] = alertType;

    String jsonData;
    serializeJson(doc, jsonData);

    Serial.println("Sending alert: " + jsonData);

    int httpCode = http.POST(jsonData);

    if (httpCode == 200) {
        Serial.println("✅ Alert sent successfully");
        alertCount++;
        flashAlert();
        beepBuzzer();
    } else {
        Serial.println("❌ Alert failed: " + String(httpCode));
    }

    http.end();
}

// ============================================
// BUTTON HANDLER
// ============================================

void checkButton() {
    int reading = digitalRead(BUTTON_PIN);

    if (reading != lastButtonState) {
        lastDebounceTime = millis();
    }

    if ((millis() - lastDebounceTime) > 50) {
        if (reading == LOW && !buttonPressed) {
            buttonPressed = true;
            Serial.println("🔘 Button pressed!");
            sendAlert("button_press");
        }
        if (reading == HIGH) {
            buttonPressed = false;
        }
    }
    lastButtonState = reading;
}

// ============================================
// WIFI CONNECTION
// ============================================

void connectWiFi() {
    Serial.println("Connecting to WiFi...");
    Serial.println("SSID: " + String(ssid));

    WiFi.mode(WIFI_STA);
    WiFi.begin(ssid, password);

    int attempt = 0;
    while (WiFi.status() != WL_CONNECTED && attempt < 40) {
        delay(500);
        Serial.print(".");

        if (attempt % 2 == 0) {
            setAllLEDs(0, 0, 255);
        } else {
            setAllLEDs(0, 0, 0);
        }
        attempt++;
    }

    Serial.println();

    if (WiFi.status() == WL_CONNECTED) {
        Serial.println("✅ WiFi Connected!");
        Serial.println("IP: " + WiFi.localIP().toString());

        for (int i = 0; i < 3; i++) {
            setAllLEDs(0, 255, 0);
            delay(200);
            setAllLEDs(0, 0, 0);
            delay(200);
        }
    } else {
        Serial.println("❌ WiFi Failed!");

        for (int i = 0; i < 5; i++) {
            setAllLEDs(255, 0, 0);
            delay(200);
            setAllLEDs(0, 0, 0);
            delay(200);
        }
    }
}

// ============================================
// SETUP
// ============================================

void setup() {
    Serial.begin(115200);
    delay(1000);

    startTime = millis();

    Serial.println();
    Serial.println("╔════════════════════════════════════════════╗");
    Serial.println("║   ESP32 Factory Light - Web Interface      ║");
    Serial.println("╚════════════════════════════════════════════╝");
    Serial.println();
    Serial.println("Table: " + String(tableNumber));
    Serial.println("Server: " + String(serverHost));
    Serial.println("Poll Interval: " + String(POLL_INTERVAL) + "ms");
    Serial.println();

    // Initialize LED strip
    strip.begin();
    strip.setBrightness(BRIGHTNESS);
    strip.show();

    // Initialize button & buzzer
    pinMode(BUTTON_PIN, INPUT_PULLUP);
    pinMode(BUZZER_PIN, OUTPUT);
    digitalWrite(BUZZER_PIN, LOW);

    // Connect WiFi
    connectWiFi();

    // Setup Web Server routes
    server.on("/", handleRoot);
    server.on("/set", handleSetColor);
    server.on("/alert", handleAlert);
    server.on("/beep", handleBeep);
    server.on("/flash", handleFlash);
    server.on("/status", handleStatus);
    server.onNotFound(handleNotFound);

    // Start Web Server
    server.begin();
    Serial.println("✅ Web Server started!");
    Serial.println("🌐 Open in browser: http://" + WiFi.localIP().toString());

    // Initial status report
    if (WiFi.status() == WL_CONNECTED) {
        sendStatus();
        pollServer();
    }

    Serial.println();
    Serial.println("🚀 Ready!");
    Serial.println("   - Web Interface: http://" + WiFi.localIP().toString());
    Serial.println("   - Polling server every " + String(POLL_INTERVAL/1000) + " seconds");
}

// ============================================
// LOOP
// ============================================

void loop() {
    // Handle web server requests
    server.handleClient();

    unsigned long now = millis();

    // Poll Laravel server for commands
    if (now - lastPollTime >= POLL_INTERVAL) {
        lastPollTime = now;
        pollServer();
    }

    // Send status every 30 seconds
    if (now - lastStatusTime >= 30000) {
        lastStatusTime = now;
        sendStatus();
    }

    // Check physical button
    checkButton();

    // Check WiFi and reconnect if needed
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("WiFi disconnected, reconnecting...");
        connectWiFi();
    }

    delay(10);
}
