/*
 * ============================================
 * ESP32 + WS2812B - FINAL v5 (Beautiful UI)
 * Factory Table Light Indicator System
 * ============================================
 */

#include <WiFi.h>
#include <WebServer.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <Adafruit_NeoPixel.h>

// ============================================
// CONFIGURATION - CHANGE THESE VALUES
// ============================================

const char* ssid = "YOUR SSID";
const char* password = "YOUR WIFI PASSWORD";

const char* serverHost = "http://192.168.0.101:8000";
const char* tableNumber = "T005";
const char* deviceId = "ESP32_005";

const int POLL_INTERVAL = 3000;
const unsigned long ALERT_DURATION = 60000;  // 1 min (300000 = 5 min)

#define LED_PIN     5
#define NUM_LEDS    10
#define BRIGHTNESS  150
#define BUTTON_PIN  4

// ============================================
// GLOBAL VARIABLES
// ============================================

Adafruit_NeoPixel strip(NUM_LEDS, LED_PIN, NEO_GRB + NEO_KHZ800);
WebServer server(80);

String currentColor = "off";
String colorBeforeAlert = "off";

unsigned long lastPollTime = 0;
unsigned long startTime = 0;

bool buttonPressed = false;
bool lastButtonState = HIGH;
unsigned long lastDebounceTime = 0;

bool alertActive = false;
unsigned long alertStartTime = 0;

int alertCount = 0;
int commandCount = 0;

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

    if (color == "red") setAllLEDs(255, 0, 0);
    else if (color == "green") setAllLEDs(0, 255, 0);
    else if (color == "blue") setAllLEDs(0, 0, 255);
    else if (color == "yellow") setAllLEDs(255, 255, 0);
    else if (color == "white") setAllLEDs(255, 255, 255);
    else { setAllLEDs(0, 0, 0); currentColor = "off"; }

    Serial.println("[LED] " + color);
}

// Get uptime string
String getUptime() {
    unsigned long sec = (millis() - startTime) / 1000;
    unsigned long min = sec / 60;
    unsigned long hr = min / 60;

    if (hr > 0) return String(hr) + "h " + String(min % 60) + "m";
    if (min > 0) return String(min) + "m " + String(sec % 60) + "s";
    return String(sec) + "s";
}

// ============================================
// HTML WEB INTERFACE - BEAUTIFUL UI
// ============================================

String getWebPage() {
    String alertRemaining = "";
    int alertPercent = 0;

    if (alertActive) {
        unsigned long elapsed = millis() - alertStartTime;
        if (elapsed < ALERT_DURATION) {
            unsigned long remaining = (ALERT_DURATION - elapsed) / 1000;
            alertRemaining = String(remaining) + "s";
            alertPercent = 100 - ((elapsed * 100) / ALERT_DURATION);
        }
    }

    // Get LED glow color for CSS
    String glowColor = "#333";
    if (currentColor == "red") glowColor = "#e74c3c";
    else if (currentColor == "green") glowColor = "#2ecc71";
    else if (currentColor == "blue") glowColor = "#3498db";
    else if (currentColor == "yellow") glowColor = "#f1c40f";
    else if (currentColor == "white") glowColor = "#ffffff";

    String html = R"rawliteral(
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>)rawliteral" + String(tableNumber) + R"rawliteral( - Factory Light</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #0f0f1a 0%, #1a1a2e 50%, #16213e 100%);
            min-height: 100vh;
            color: #fff;
            padding: 15px;
            padding-bottom: 80px;
        }

        .container {
            max-width: 420px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            text-align: center;
            padding: 25px 20px;
            background: linear-gradient(145deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            border-radius: 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
        }

        .header-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .table-badge {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 12px 30px;
            border-radius: 30px;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 2px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .device-id {
            margin-top: 12px;
            font-size: 12px;
            color: rgba(255,255,255,0.5);
            font-family: monospace;
        }

        /* Alert Banner */
        .alert-banner {
            background: linear-gradient(135deg, #f39c12 0%, #e74c3c 100%);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
            animation: alertPulse 1.5s ease-in-out infinite;
            box-shadow: 0 4px 20px rgba(243, 156, 18, 0.4);
        }

        @keyframes alertPulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.02); opacity: 0.9; }
        }

        .alert-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .alert-timer {
            font-size: 32px;
            font-weight: 700;
            margin: 10px 0;
        }

        .alert-restore {
            font-size: 13px;
            opacity: 0.9;
        }

        .progress-bar {
            height: 6px;
            background: rgba(0,0,0,0.2);
            border-radius: 3px;
            margin-top: 15px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: #fff;
            border-radius: 3px;
            transition: width 1s linear;
        }

        /* LED Display Card */
        .led-card {
            background: linear-gradient(145deg, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0.03) 100%);
            border-radius: 24px;
            padding: 30px 20px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .led-container {
            position: relative;
            width: 140px;
            height: 140px;
            margin: 0 auto 20px;
        }

        .led-glow {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: )rawliteral" + glowColor + R"rawliteral(;
            filter: blur(30px);
            opacity: )rawliteral" + String(currentColor == "off" ? "0" : "0.6") + R"rawliteral(;
            animation: )rawliteral" + String(currentColor == "off" ? "none" : "glowPulse 2s ease-in-out infinite") + R"rawliteral(;
        }

        @keyframes glowPulse {
            0%, 100% { opacity: 0.4; transform: translate(-50%, -50%) scale(1); }
            50% { opacity: 0.7; transform: translate(-50%, -50%) scale(1.1); }
        }

        .led-bulb {
            position: relative;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 10px auto;
            border: 4px solid rgba(255,255,255,0.2);
            transition: all 0.3s ease;
        }

        .led-off { background: linear-gradient(145deg, #2a2a3a 0%, #1a1a2a 100%); }
        .led-red { background: linear-gradient(145deg, #ff6b6b 0%, #c0392b 100%); }
        .led-green { background: linear-gradient(145deg, #6bff8b 0%, #27ae60 100%); }
        .led-blue { background: linear-gradient(145deg, #6b9bff 0%, #2980b9 100%); }
        .led-yellow { background: linear-gradient(145deg, #fff06b 0%, #f39c12 100%); }
        .led-white { background: linear-gradient(145deg, #ffffff 0%, #bdc3c7 100%); }

        .led-label {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: rgba(255,255,255,0.6);
            margin-bottom: 8px;
        }

        .led-status {
            font-size: 28px;
            font-weight: 700;
            text-transform: uppercase;
            color: )rawliteral" + glowColor + R"rawliteral(;
        }

        /* Control Panel */
        .control-panel {
            background: linear-gradient(145deg, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0.03) 100%);
            border-radius: 24px;
            padding: 25px 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .panel-title {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: rgba(255,255,255,0.5);
            margin-bottom: 20px;
            text-align: center;
        }

        .color-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        .color-btn {
            aspect-ratio: 1;
            border: none;
            border-radius: 16px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            color: #fff;
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }

        .color-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.4);
        }

        .color-btn:active {
            transform: translateY(0);
        }

        .color-btn.active {
            border: 3px solid #fff;
            box-shadow: 0 0 20px currentColor;
        }

        .btn-red { background: linear-gradient(145deg, #e74c3c 0%, #c0392b 100%); }
        .btn-green { background: linear-gradient(145deg, #2ecc71 0%, #27ae60 100%); }
        .btn-blue { background: linear-gradient(145deg, #3498db 0%, #2980b9 100%); }
        .btn-yellow { background: linear-gradient(145deg, #f1c40f 0%, #f39c12 100%); color: #333; }
        .btn-white { background: linear-gradient(145deg, #ecf0f1 0%, #bdc3c7 100%); color: #333; }
        .btn-off { background: linear-gradient(145deg, #34495e 0%, #2c3e50 100%); border: 1px solid rgba(255,255,255,0.2); }

        .color-dot {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.3);
        }

        .dot-red { background: #e74c3c; }
        .dot-green { background: #2ecc71; }
        .dot-blue { background: #3498db; }
        .dot-yellow { background: #f1c40f; }
        .dot-white { background: #fff; }
        .dot-off { background: #333; }

        /* Action Buttons */
        .action-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .action-btn {
            padding: 16px;
            border: none;
            border-radius: 14px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .action-btn:hover {
            transform: translateY(-2px);
        }

        .btn-clear {
            background: linear-gradient(145deg, #e74c3c 0%, #c0392b 100%);
            color: #fff;
        }

        .btn-refresh {
            background: linear-gradient(145deg, #1abc9c 0%, #16a085 100%);
            color: #fff;
        }

        /* Status Cards */
        .status-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        .status-card {
            background: linear-gradient(145deg, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0.03) 100%);
            border-radius: 16px;
            padding: 18px 15px;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .status-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255,255,255,0.5);
            margin-bottom: 8px;
        }

        .status-value {
            font-size: 18px;
            font-weight: 600;
        }

        .status-value.online {
            color: #2ecc71;
        }

        /* Info Section */
        .info-section {
            background: rgba(255,255,255,0.03);
            border-radius: 16px;
            padding: 18px;
            border: 1px solid rgba(255,255,255,0.05);
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            font-size: 13px;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: rgba(255,255,255,0.5);
        }

        .info-value {
            color: rgba(255,255,255,0.9);
            font-weight: 500;
            font-family: monospace;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 11px;
            color: rgba(255,255,255,0.3);
        }

        /* Auto-refresh indicator */
        .refresh-indicator {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(0,0,0,0.7);
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 11px;
            color: rgba(255,255,255,0.7);
            backdrop-filter: blur(10px);
        }

        .refresh-dot {
            display: inline-block;
            width: 6px;
            height: 6px;
            background: #2ecc71;
            border-radius: 50%;
            margin-right: 6px;
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-icon">&#127981;</div>
            <div class="table-badge">)rawliteral" + String(tableNumber) + R"rawliteral(</div>
            <div class="device-id">)rawliteral" + String(deviceId) + R"rawliteral(</div>
        </div>

        <!-- Alert Banner (if active) -->
        )rawliteral" + (alertActive ? R"rawliteral(
        <div class="alert-banner">
            <div class="alert-title">&#9888; ALERT ACTIVE</div>
            <div class="alert-timer">)rawliteral" + alertRemaining + R"rawliteral(</div>
            <div class="alert-restore">Will restore to: <strong>)rawliteral" + colorBeforeAlert + R"rawliteral(</strong></div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: )rawliteral" + String(alertPercent) + R"rawliteral(%;"></div>
            </div>
        </div>
        )rawliteral" : "") + R"rawliteral(

        <!-- LED Display -->
        <div class="led-card">
            <div class="led-container">
                <div class="led-glow"></div>
                <div class="led-bulb led-)rawliteral" + currentColor + R"rawliteral("></div>
            </div>
            <div class="led-label">Current Status</div>
            <div class="led-status">)rawliteral" + currentColor + R"rawliteral(</div>
        </div>

        <!-- Control Panel -->
        <div class="control-panel">
            <div class="panel-title">LED Control</div>
            <div class="color-grid">
                <button class="color-btn btn-red )rawliteral" + String(currentColor == "red" ? "active" : "") + R"rawliteral(" onclick="setColor('red')">
                    <div class="color-dot dot-red"></div>
                    <span>Red</span>
                </button>
                <button class="color-btn btn-green )rawliteral" + String(currentColor == "green" ? "active" : "") + R"rawliteral(" onclick="setColor('green')">
                    <div class="color-dot dot-green"></div>
                    <span>Green</span>
                </button>
                <button class="color-btn btn-blue )rawliteral" + String(currentColor == "blue" ? "active" : "") + R"rawliteral(" onclick="setColor('blue')">
                    <div class="color-dot dot-blue"></div>
                    <span>Blue</span>
                </button>
                <button class="color-btn btn-yellow )rawliteral" + String(currentColor == "yellow" ? "active" : "") + R"rawliteral(" onclick="setColor('yellow')">
                    <div class="color-dot dot-yellow"></div>
                    <span>Yellow</span>
                </button>
                <button class="color-btn btn-white )rawliteral" + String(currentColor == "white" ? "active" : "") + R"rawliteral(" onclick="setColor('white')">
                    <div class="color-dot dot-white"></div>
                    <span>White</span>
                </button>
                <button class="color-btn btn-off )rawliteral" + String(currentColor == "off" ? "active" : "") + R"rawliteral(" onclick="setColor('off')">
                    <div class="color-dot dot-off"></div>
                    <span>Off</span>
                </button>
            </div>
            <div class="action-grid">
                <button class="action-btn btn-clear" onclick="clearAlert()">
                    <span>&#10006;</span> Clear Alert
                </button>
                <button class="action-btn btn-refresh" onclick="location.reload()">
                    <span>&#8635;</span> Refresh
                </button>
            </div>
        </div>

        <!-- Status Cards -->
        <div class="status-grid">
            <div class="status-card">
                <div class="status-label">WiFi Status</div>
                <div class="status-value online">)rawliteral" + String(WiFi.status() == WL_CONNECTED ? "Connected" : "Disconnected") + R"rawliteral(</div>
            </div>
            <div class="status-card">
                <div class="status-label">Signal</div>
                <div class="status-value">)rawliteral" + String(WiFi.RSSI()) + R"rawliteral( dBm</div>
            </div>
            <div class="status-card">
                <div class="status-label">Uptime</div>
                <div class="status-value">)rawliteral" + getUptime() + R"rawliteral(</div>
            </div>
            <div class="status-card">
                <div class="status-label">Alerts Sent</div>
                <div class="status-value">)rawliteral" + String(alertCount) + R"rawliteral(</div>
            </div>
        </div>

        <!-- Info Section -->
        <div class="info-section">
            <div class="info-row">
                <span class="info-label">IP Address</span>
                <span class="info-value">)rawliteral" + WiFi.localIP().toString() + R"rawliteral(</span>
            </div>
            <div class="info-row">
                <span class="info-label">Saved Color</span>
                <span class="info-value">)rawliteral" + colorBeforeAlert + R"rawliteral(</span>
            </div>
            <div class="info-row">
                <span class="info-label">Alert Duration</span>
                <span class="info-value">)rawliteral" + String(ALERT_DURATION / 1000) + R"rawliteral(s</span>
            </div>
            <div class="info-row">
                <span class="info-label">Server</span>
                <span class="info-value" style="font-size:10px;">)rawliteral" + String(serverHost) + R"rawliteral(</span>
            </div>
        </div>

        <div class="footer">
            ESP32 Factory Light Controller v5.0
        </div>
    </div>

    <div class="refresh-indicator">
        <span class="refresh-dot"></span>Auto-refresh: 3s
    </div>

    <script>
        function setColor(color) {
            fetch('/set?color=' + color)
                .then(function(response) { return response.text(); })
                .then(function() { location.reload(); });
        }

        function clearAlert() {
            fetch('/clear')
                .then(function(response) { return response.text(); })
                .then(function() { location.reload(); });
        }

        // Auto refresh every 3 seconds
        setTimeout(function() { location.reload(); }, 3000);
    </script>
</body>
</html>
)rawliteral";
    return html;
}

// ============================================
// WEB HANDLERS
// ============================================

void handleRoot() {
    server.send(200, "text/html", getWebPage());
}

void handleSet() {
    if (server.hasArg("color")) {
        String color = server.arg("color");
        alertActive = false;
        setColor(color);
        colorBeforeAlert = color;
    }
    server.send(200, "text/plain", "OK");
}

void handleClear() {
    alertActive = false;
    setColor(colorBeforeAlert);
    server.send(200, "text/plain", "OK");
}

void handleStatus() {
    StaticJsonDocument<256> doc;
    doc["table"] = tableNumber;
    doc["device_id"] = deviceId;
    doc["color"] = currentColor;
    doc["saved_color"] = colorBeforeAlert;
    doc["alert"] = alertActive;
    doc["uptime"] = millis() - startTime;
    doc["rssi"] = WiFi.RSSI();

    String response;
    serializeJson(doc, response);
    server.send(200, "application/json", response);
}

// ============================================
// SERVER COMMUNICATION
// ============================================

void pollServer() {
    if (WiFi.status() != WL_CONNECTED) return;

    HTTPClient http;
    String url = String(serverHost) + "/api/esp32/poll?table=" + tableNumber + "&device_id=" + deviceId;

    http.begin(url);
    http.setTimeout(5000);

    int httpCode = http.GET();

    if (httpCode == 200) {
        String response = http.getString();

        // If alert is active, just update last_seen but ignore commands
        if (alertActive) {
            Serial.println("[POLL] Alert active - ignoring commands");
            http.end();
            return;
        }

        StaticJsonDocument<512> doc;
        DeserializationError error = deserializeJson(doc, response);

        if (!error && doc["success"]) {
            if (!doc["command"].isNull()) {
                String cmdColor = doc["command"]["color"] | "";

                if (cmdColor.length() > 0 && cmdColor != "null") {
                    Serial.println("[CMD] " + cmdColor);
                    setColor(cmdColor);
                    colorBeforeAlert = cmdColor;
                    commandCount++;
                }
            }
        }
    }

    http.end();
}

void sendAlert() {
    if (WiFi.status() != WL_CONNECTED) return;

    HTTPClient http;
    String url = String(serverHost) + "/api/esp32/alert";

    http.begin(url);
    http.addHeader("Content-Type", "application/json");
    http.setTimeout(5000);

    StaticJsonDocument<256> doc;
    doc["table_number"] = tableNumber;
    doc["device_id"] = deviceId;
    doc["alert_type"] = "button_press";
    doc["previous_color"] = colorBeforeAlert;

    String jsonData;
    serializeJson(doc, jsonData);

    Serial.println("[ALERT] " + jsonData);

    int httpCode = http.POST(jsonData);
    if (httpCode == 200) {
        Serial.println("[OK] Alert sent");
        alertCount++;
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

            colorBeforeAlert = currentColor;

            Serial.println("");
            Serial.println("========================================");
            Serial.println("[BUTTON] PRESSED!");
            Serial.println("  Current: " + currentColor);
            Serial.println("  Saved:   " + colorBeforeAlert);
            Serial.println("  Timer:   " + String(ALERT_DURATION / 1000) + " seconds");
            Serial.println("========================================");

            setColor("yellow");

            alertActive = true;
            alertStartTime = millis();

            sendAlert();
        }
        if (reading == HIGH) {
            buttonPressed = false;
        }
    }
    lastButtonState = reading;
}

// ============================================
// ALERT TIMEOUT
// ============================================

void checkAlertTimeout() {
    if (!alertActive) return;

    if (millis() - alertStartTime >= ALERT_DURATION) {
        Serial.println("");
        Serial.println("========================================");
        Serial.println("[TIMEOUT] Alert finished!");
        Serial.println("  Restoring to: " + colorBeforeAlert);
        Serial.println("========================================");

        alertActive = false;
        setColor(colorBeforeAlert);

        Serial.println("[OK] Restored to: " + colorBeforeAlert);
        Serial.println("");
    }
}

// ============================================
// WIFI
// ============================================

void connectWiFi() {
    Serial.print("[WIFI] Connecting to " + String(ssid));
    WiFi.mode(WIFI_STA);
    WiFi.begin(ssid, password);

    int attempt = 0;
    while (WiFi.status() != WL_CONNECTED && attempt < 40) {
        delay(500);
        Serial.print(".");
        setAllLEDs(0, 0, attempt % 2 == 0 ? 255 : 0);
        attempt++;
    }
    Serial.println("");

    if (WiFi.status() == WL_CONNECTED) {
        Serial.println("[OK] IP: " + WiFi.localIP().toString());
        for (int i = 0; i < 3; i++) {
            setAllLEDs(0, 255, 0); delay(200);
            setAllLEDs(0, 0, 0); delay(200);
        }
        setColor("off");
    } else {
        Serial.println("[FAIL] WiFi connection failed");
    }
}

// ============================================
// SETUP
// ============================================

void setup() {
    Serial.begin(115200);
    delay(1000);

    startTime = millis();

    Serial.println("");
    Serial.println("========================================");
    Serial.println("  ESP32 Factory Light v5 (Beautiful UI)");
    Serial.println("========================================");
    Serial.println("");
    Serial.println("Table: " + String(tableNumber));
    Serial.println("Device: " + String(deviceId));
    Serial.println("Alert Duration: " + String(ALERT_DURATION / 1000) + " seconds");
    Serial.println("");

    strip.begin();
    strip.setBrightness(BRIGHTNESS);
    strip.show();

    pinMode(BUTTON_PIN, INPUT_PULLUP);

    connectWiFi();

    server.on("/", handleRoot);
    server.on("/set", handleSet);
    server.on("/clear", handleClear);
    server.on("/status", handleStatus);
    server.begin();

    Serial.println("[WEB] http://" + WiFi.localIP().toString());
    Serial.println("[READY] Press button to test");
    Serial.println("");
}

// ============================================
// LOOP
// ============================================

void loop() {
    server.handleClient();

    checkAlertTimeout();

    if (millis() - lastPollTime >= POLL_INTERVAL) {
        lastPollTime = millis();
        pollServer();
    }

    checkButton();

    if (WiFi.status() != WL_CONNECTED) {
        connectWiFi();
    }

    delay(10);
}
