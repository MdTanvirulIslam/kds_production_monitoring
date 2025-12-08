/*
 * ============================================
 * ESP32 + WS2812B - cPanel Server Polling
 * Factory Table Light Indicator System
 * ============================================
 *
 * This version POLLS the server for commands
 * instead of waiting for incoming connections.
 *
 * Works with cPanel hosted Laravel!
 */

#include <WiFi.h>
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
const char* serverHost = "http://kds.differentcoder.website";  // ← Change to your domain
const char* tableNumber = "T005";                    // ← Change to your table number
const char* deviceId = "ESP32_005";                  // ← Unique device ID

// Polling interval (milliseconds)
const int POLL_INTERVAL = 1000;  // Poll every 2 seconds

// LED Strip Settings
#define LED_PIN     5
#define NUM_LEDS    10
#define BRIGHTNESS  150

// Button & Buzzer (optional)
#define BUTTON_PIN  4
#define BUZZER_PIN  18

// ============================================
// GLOBAL VARIABLES
// ============================================

Adafruit_NeoPixel strip(NUM_LEDS, LED_PIN, NEO_GRB + NEO_KHZ800);

String currentColor = "off";
unsigned long lastPollTime = 0;
unsigned long lastStatusTime = 0;

// Button debounce
bool buttonPressed = false;
bool lastButtonState = HIGH;
unsigned long lastDebounceTime = 0;

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
// SERVER COMMUNICATION
// ============================================

// Poll server for commands
void pollServer() {
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("WiFi not connected, skipping poll");
        return;
    }

    HTTPClient http;

    // Build poll URL
    String url = String(serverHost) + "/api/esp32/poll?table=" + tableNumber + "&device_id=" + deviceId;

    Serial.println("Polling: " + url);

    http.begin(url);
    http.setTimeout(5000);

    int httpCode = http.GET();

    if (httpCode == 200) {
        String response = http.getString();
        Serial.println("Response: " + response);

        // Parse JSON response
        StaticJsonDocument<512> doc;
        DeserializationError error = deserializeJson(doc, response);

        if (!error) {
            bool success = doc["success"] | false;

            if (success) {
                // Check for command
                if (!doc["command"].isNull()) {
                    String cmdColor = doc["command"]["color"] | "off";
                    bool cmdBlink = doc["command"]["blink"] | false;

                    Serial.println("📥 Command received: " + cmdColor);

                    // Execute command
                    setColor(cmdColor);

                    // Beep on command
                    digitalWrite(BUZZER_PIN, HIGH);
                    delay(50);
                    digitalWrite(BUZZER_PIN, LOW);
                }

                // Sync with server's current color if no command
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

// Send status to server
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

// Send alert to server (button press)
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

        // Blink blue while connecting
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

        // Flash green on success
        for (int i = 0; i < 3; i++) {
            setAllLEDs(0, 255, 0);
            delay(200);
            setAllLEDs(0, 0, 0);
            delay(200);
        }
    } else {
        Serial.println("❌ WiFi Failed!");

        // Flash red on failure
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

    Serial.println();
    Serial.println("╔════════════════════════════════════════╗");
    Serial.println("║   Factory Light - Server Polling       ║");
    Serial.println("╚════════════════════════════════════════╝");
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

    // Initial status report
    if (WiFi.status() == WL_CONNECTED) {
        sendStatus();
        pollServer();  // Get initial state
    }

    Serial.println();
    Serial.println("🚀 Ready! Polling server every " + String(POLL_INTERVAL/1000) + " seconds");
}

// ============================================
// LOOP
// ============================================

void loop() {
    unsigned long now = millis();

    // Poll server for commands
    if (now - lastPollTime >= POLL_INTERVAL) {
        lastPollTime = now;
        pollServer();
    }

    // Send status every 30 seconds
    if (now - lastStatusTime >= 30000) {
        lastStatusTime = now;
        sendStatus();
    }

    // Check button
    checkButton();

    // Check WiFi and reconnect if needed
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("WiFi disconnected, reconnecting...");
        connectWiFi();
    }

    delay(10);
}
