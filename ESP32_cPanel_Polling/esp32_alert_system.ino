/*
 * =====================================================
 * ESP32 Alert System - WS2812B LED + Push Button
 * =====================================================
 * 
 * Circuit Connections:
 * - GPIO 5  → 370Ω Resistor → WS2812B DIN
 * - GPIO 4  → Push Button → GND (using internal pullup)
 * - GND     → Common ground
 * - 5V PSU  → WS2812B +5V
 * 
 * Libraries Required:
 * - FastLED (Install from Arduino Library Manager)
 * - ArduinoJson
 * 
 * =====================================================
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <FastLED.h>

// ==================== CONFIGURATION ====================

// WiFi Settings - CHANGE THESE!
const char* WIFI_SSID = "YOUR_WIFI_SSID";
const char* WIFI_PASSWORD = "YOUR_WIFI_PASSWORD";

// Laravel Server Settings - CHANGE THESE!
const char* SERVER_URL = "http://kds.differentcoder.website/api/esp32";
const char* TABLE_ID = "1";           // Change for each table
const char* TABLE_NUMBER = "T-001";   // Change for each table

// ==================== PIN DEFINITIONS ====================

#define LED_PIN         5       // GPIO 5 - WS2812B Data Pin
#define BUTTON_PIN      4       // GPIO 4 - Alert Push Button
#define NUM_LEDS        8       // Number of LEDs in strip
#define LED_TYPE        WS2812B
#define COLOR_ORDER     GRB
#define BRIGHTNESS      150     // 0-255

// ==================== LED ARRAY ====================

CRGB leds[NUM_LEDS];

// ==================== COLOR DEFINITIONS ====================

#define COLOR_RED       CRGB(255, 0, 0)
#define COLOR_GREEN     CRGB(0, 255, 0)
#define COLOR_BLUE      CRGB(0, 0, 255)
#define COLOR_YELLOW    CRGB(255, 200, 0)
#define COLOR_WHITE     CRGB(255, 255, 255)
#define COLOR_OFF       CRGB(0, 0, 0)

// ==================== VARIABLES ====================

// Button state
bool lastButtonState = HIGH;  // HIGH because of INPUT_PULLUP
bool buttonPressed = false;

// Debounce
unsigned long lastDebounceTime = 0;
const unsigned long DEBOUNCE_DELAY = 200;

// Server polling
unsigned long lastPollTime = 0;
const unsigned long POLL_INTERVAL = 3000;  // Poll every 3 seconds

// Current LED state
String currentLedColor = "off";
bool alertActive = false;

// Animation
unsigned long lastAnimationTime = 0;

// ==================== SETUP ====================

void setup() {
    Serial.begin(115200);
    Serial.println("\n");
    Serial.println("=============================================");
    Serial.println("   ESP32 Alert System - Starting Up");
    Serial.println("=============================================");
    
    // Initialize FastLED
    FastLED.addLeds<LED_TYPE, LED_PIN, COLOR_ORDER>(leds, NUM_LEDS).setCorrection(TypicalLEDStrip);
    FastLED.setBrightness(BRIGHTNESS);
    FastLED.clear();
    FastLED.show();
    Serial.println("✓ LED Strip initialized");
    
    // Initialize button with internal pull-up resistor
    pinMode(BUTTON_PIN, INPUT_PULLUP);
    Serial.println("✓ Button initialized (GPIO 4)");
    
    // Startup animation
    startupAnimation();
    
    // Connect to WiFi
    connectWiFi();
    
    // Register with server
    registerWithServer();
    
    Serial.println("\n=============================================");
    Serial.println("   System Ready! Press button to send alert");
    Serial.println("=============================================\n");
}

// ==================== MAIN LOOP ====================

void loop() {
    // Check WiFi connection
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("WiFi disconnected! Reconnecting...");
        connectWiFi();
    }
    
    // Check button
    checkButton();
    
    // Poll server for LED commands
    if (millis() - lastPollTime >= POLL_INTERVAL) {
        pollServer();
        lastPollTime = millis();
    }
    
    // Run alert animation if active
    if (alertActive) {
        alertAnimation();
    }
    
    delay(10);
}

// ==================== WIFI FUNCTIONS ====================

void connectWiFi() {
    Serial.print("Connecting to WiFi: ");
    Serial.println(WIFI_SSID);
    
    WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
    
    // Show connecting animation
    int attempt = 0;
    while (WiFi.status() != WL_CONNECTED && attempt < 30) {
        // Blue chase animation while connecting
        leds[attempt % NUM_LEDS] = COLOR_BLUE;
        FastLED.show();
        delay(200);
        leds[attempt % NUM_LEDS] = COLOR_OFF;
        FastLED.show();
        
        Serial.print(".");
        attempt++;
    }
    
    if (WiFi.status() == WL_CONNECTED) {
        Serial.println("\n✓ WiFi Connected!");
        Serial.print("  IP Address: ");
        Serial.println(WiFi.localIP());
        
        // Green success flash
        successAnimation();
    } else {
        Serial.println("\n✗ WiFi Connection Failed!");
        errorAnimation();
    }
}

// ==================== BUTTON FUNCTIONS ====================

void checkButton() {
    // Read button (LOW when pressed due to INPUT_PULLUP)
    bool currentState = digitalRead(BUTTON_PIN);
    
    // Check for state change with debounce
    if (currentState != lastButtonState) {
        if (millis() - lastDebounceTime > DEBOUNCE_DELAY) {
            lastDebounceTime = millis();
            
            // Button pressed (goes LOW)
            if (currentState == LOW) {
                Serial.println("\n🔴 ALERT BUTTON PRESSED!");
                
                // Visual feedback
                buttonPressAnimation();
                
                // Send notification to server
                sendAlertNotification();
                
                // Activate alert mode
                alertActive = true;
                currentLedColor = "red";
            }
            
            lastButtonState = currentState;
        }
    }
}

// ==================== HTTP FUNCTIONS ====================

void sendAlertNotification() {
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("✗ Cannot send - WiFi not connected");
        return;
    }
    
    Serial.println("Sending alert to server...");
    
    HTTPClient http;
    String url = String(SERVER_URL) + "/button-press";
    
    http.begin(url);
    http.addHeader("Content-Type", "application/json");
    http.addHeader("Accept", "application/json");
    http.setTimeout(5000);
    
    // Create JSON payload
    StaticJsonDocument<512> doc;
    doc["table_id"] = TABLE_ID;
    doc["table_number"] = TABLE_NUMBER;
    doc["button_color"] = "red";
    doc["notification_type"] = "alert";
    doc["message"] = "Worker pressed ALERT button - Needs assistance!";
    doc["ip_address"] = WiFi.localIP().toString();
    doc["timestamp"] = millis();
    
    String payload;
    serializeJson(doc, payload);
    
    Serial.print("  URL: ");
    Serial.println(url);
    
    int httpCode = http.POST(payload);
    
    if (httpCode > 0) {
        Serial.print("  Response: ");
        Serial.println(httpCode);
        
        if (httpCode == HTTP_CODE_OK) {
            Serial.println("  ✓ Alert sent successfully!");
        }
    } else {
        Serial.print("  ✗ Error: ");
        Serial.println(http.errorToString(httpCode));
    }
    
    http.end();
}

void registerWithServer() {
    if (WiFi.status() != WL_CONNECTED) return;
    
    Serial.println("Registering with server...");
    
    HTTPClient http;
    String url = String(SERVER_URL) + "/register";
    
    http.begin(url);
    http.addHeader("Content-Type", "application/json");
    http.setTimeout(5000);
    
    StaticJsonDocument<256> doc;
    doc["table_id"] = TABLE_ID;
    doc["table_number"] = TABLE_NUMBER;
    doc["ip_address"] = WiFi.localIP().toString();
    doc["has_buttons"] = true;
    doc["led_type"] = "WS2812B";
    doc["num_leds"] = NUM_LEDS;
    doc["firmware_version"] = "1.0.0";
    
    String payload;
    serializeJson(doc, payload);
    
    int httpCode = http.POST(payload);
    
    if (httpCode == HTTP_CODE_OK) {
        Serial.println("✓ Registered with server");
    } else {
        Serial.println("✗ Registration failed (server may be offline)");
    }
    
    http.end();
}

void pollServer() {
    if (WiFi.status() != WL_CONNECTED) return;
    
    HTTPClient http;
    String url = String(SERVER_URL) + "/poll?table_id=" + TABLE_ID;
    
    http.begin(url);
    http.addHeader("Accept", "application/json");
    http.setTimeout(3000);
    
    int httpCode = http.GET();
    
    if (httpCode == HTTP_CODE_OK) {
        String response = http.getString();
        
        StaticJsonDocument<256> doc;
        DeserializationError error = deserializeJson(doc, response);
        
        if (!error) {
            String newColor = doc["led_color"] | "off";
            
            // Check if color changed from server
            if (newColor != currentLedColor) {
                Serial.print("Server command: ");
                Serial.println(newColor);
                
                currentLedColor = newColor;
                
                if (newColor == "off") {
                    alertActive = false;
                    setLEDColor(COLOR_OFF);
                } else if (newColor == "red") {
                    alertActive = true;
                } else if (newColor == "green") {
                    alertActive = false;
                    setLEDColor(COLOR_GREEN);
                } else if (newColor == "blue") {
                    alertActive = false;
                    setLEDColor(COLOR_BLUE);
                } else if (newColor == "yellow") {
                    alertActive = false;
                    setLEDColor(COLOR_YELLOW);
                }
            }
        }
    }
    
    http.end();
}

// ==================== LED FUNCTIONS ====================

void setLEDColor(CRGB color) {
    fill_solid(leds, NUM_LEDS, color);
    FastLED.show();
}

void setAllOff() {
    FastLED.clear();
    FastLED.show();
}

// ==================== ANIMATIONS ====================

void startupAnimation() {
    Serial.println("Playing startup animation...");
    
    // Rainbow sweep
    for (int i = 0; i < NUM_LEDS * 3; i++) {
        for (int j = 0; j < NUM_LEDS; j++) {
            leds[j] = CHSV((i * 256 / NUM_LEDS + j * 256 / NUM_LEDS) % 256, 255, 200);
        }
        FastLED.show();
        delay(50);
    }
    
    // Fade out
    for (int b = 200; b >= 0; b -= 10) {
        FastLED.setBrightness(b);
        FastLED.show();
        delay(20);
    }
    
    FastLED.setBrightness(BRIGHTNESS);
    FastLED.clear();
    FastLED.show();
}

void successAnimation() {
    // Green flash 3 times
    for (int i = 0; i < 3; i++) {
        setLEDColor(COLOR_GREEN);
        delay(150);
        setAllOff();
        delay(150);
    }
}

void errorAnimation() {
    // Red flash 5 times
    for (int i = 0; i < 5; i++) {
        setLEDColor(COLOR_RED);
        delay(100);
        setAllOff();
        delay(100);
    }
}

void buttonPressAnimation() {
    // White flash then red
    setLEDColor(COLOR_WHITE);
    delay(100);
    
    // Ripple effect
    for (int i = 0; i < NUM_LEDS; i++) {
        leds[i] = COLOR_RED;
        FastLED.show();
        delay(30);
    }
}

void alertAnimation() {
    // Pulsing red effect
    static uint8_t brightness = 0;
    static int8_t direction = 5;
    
    if (millis() - lastAnimationTime > 20) {
        lastAnimationTime = millis();
        
        brightness += direction;
        
        if (brightness >= 255) {
            brightness = 255;
            direction = -5;
        } else if (brightness <= 50) {
            brightness = 50;
            direction = 5;
        }
        
        for (int i = 0; i < NUM_LEDS; i++) {
            leds[i] = CRGB(brightness, 0, 0);
        }
        FastLED.show();
    }
}

// ==================== UTILITY FUNCTIONS ====================

void printStatus() {
    Serial.println("\n--- Current Status ---");
    Serial.print("WiFi: ");
    Serial.println(WiFi.status() == WL_CONNECTED ? "Connected" : "Disconnected");
    Serial.print("IP: ");
    Serial.println(WiFi.localIP());
    Serial.print("LED Color: ");
    Serial.println(currentLedColor);
    Serial.print("Alert Active: ");
    Serial.println(alertActive ? "Yes" : "No");
    Serial.println("----------------------\n");
}
