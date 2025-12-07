/*
 * ============================================
 * SIMPLE BUTTON TEST - Quick Debugging
 * ============================================
 * 
 * Use this code FIRST to test if your button works!
 * 
 * What it does:
 * 1. Prints button state to Serial Monitor every 100ms
 * 2. Turns ON built-in LED when button pressed
 * 3. Beeps buzzer when button pressed
 * 
 * How to test:
 * 1. Upload this code
 * 2. Open Serial Monitor (115200 baud)
 * 3. Press the button
 * 4. Watch the output change from HIGH to LOW
 * 
 * ============================================
 */

#define BUTTON_PIN  4      // GPIO 4 - Change if different
#define BUZZER_PIN  18     // GPIO 18 - Change if different
#define BUILTIN_LED 2      // ESP32 built-in LED

int lastState = HIGH;
int pressCount = 0;

void setup() {
    Serial.begin(115200);
    delay(1000);
    
    Serial.println();
    Serial.println("╔═══════════════════════════════════════╗");
    Serial.println("║      SIMPLE BUTTON TEST               ║");
    Serial.println("╚═══════════════════════════════════════╝");
    Serial.println();
    Serial.println("Button Pin: GPIO " + String(BUTTON_PIN));
    Serial.println("Buzzer Pin: GPIO " + String(BUZZER_PIN));
    Serial.println();
    Serial.println("Press the button and watch below...");
    Serial.println("─────────────────────────────────────────");
    Serial.println();
    
    // Setup pins
    pinMode(BUTTON_PIN, INPUT_PULLUP);  // Button with internal pull-up
    pinMode(BUZZER_PIN, OUTPUT);
    pinMode(BUILTIN_LED, OUTPUT);
    
    digitalWrite(BUZZER_PIN, LOW);
    digitalWrite(BUILTIN_LED, LOW);
}

void loop() {
    // Read button
    int buttonState = digitalRead(BUTTON_PIN);
    
    // Print state continuously
    Serial.print("Button GPIO ");
    Serial.print(BUTTON_PIN);
    Serial.print(" = ");
    Serial.print(buttonState);
    Serial.print(" (");
    Serial.print(buttonState == LOW ? "PRESSED 👇" : "RELEASED 👆");
    Serial.print(") | Presses: ");
    Serial.println(pressCount);
    
    // Check for button press (LOW = pressed with INPUT_PULLUP)
    if (buttonState == LOW && lastState == HIGH) {
        pressCount++;
        
        Serial.println();
        Serial.println("🔴🔴🔴 BUTTON PRESSED! 🔴🔴🔴");
        Serial.println("   Press #" + String(pressCount));
        Serial.println();
        
        // Turn ON built-in LED
        digitalWrite(BUILTIN_LED, HIGH);
        
        // Beep buzzer
        digitalWrite(BUZZER_PIN, HIGH);
        delay(100);
        digitalWrite(BUZZER_PIN, LOW);
    }
    
    // Button released
    if (buttonState == HIGH && lastState == LOW) {
        Serial.println("   ○ Button Released");
        Serial.println();
        
        // Turn OFF built-in LED
        digitalWrite(BUILTIN_LED, LOW);
    }
    
    lastState = buttonState;
    
    delay(100);  // Print every 100ms
}
