/*
 * ============================================
 * BUTTON WORKING TEST - Clear Feedback
 * ============================================
 * 
 * Your button IS WORKING! This code shows
 * clear feedback when button is pressed.
 * 
 * ============================================
 */

#define BUTTON_PIN  4
#define BUZZER_PIN  18
#define BUILTIN_LED 2

int pressCount = 0;
bool lastState = HIGH;
bool currentlyPressed = false;

void setup() {
    Serial.begin(115200);
    delay(1000);
    
    Serial.println();
    Serial.println("╔═══════════════════════════════════════╗");
    Serial.println("║     ✅ BUTTON IS WORKING!             ║");
    Serial.println("╚═══════════════════════════════════════╝");
    Serial.println();
    Serial.println("Press the button and watch the count increase!");
    Serial.println();
    
    pinMode(BUTTON_PIN, INPUT_PULLUP);
    pinMode(BUZZER_PIN, OUTPUT);
    pinMode(BUILTIN_LED, OUTPUT);
    
    digitalWrite(BUZZER_PIN, LOW);
    digitalWrite(BUILTIN_LED, LOW);
}

void loop() {
    int buttonState = digitalRead(BUTTON_PIN);
    
    // Detect NEW button press (HIGH to LOW transition)
    if (buttonState == LOW && lastState == HIGH) {
        pressCount++;
        currentlyPressed = true;
        
        // Clear feedback
        Serial.println();
        Serial.println("╔═══════════════════════════════════════╗");
        Serial.println("║                                       ║");
        Serial.println("║   🔴 BUTTON PRESSED! (#" + String(pressCount) + ")            ║");
        Serial.println("║                                       ║");
        Serial.println("╚═══════════════════════════════════════╝");
        Serial.println();
        
        // Visual feedback - LED ON
        digitalWrite(BUILTIN_LED, HIGH);
        
        // Audio feedback - BEEP
        digitalWrite(BUZZER_PIN, HIGH);
        delay(100);
        digitalWrite(BUZZER_PIN, LOW);
    }
    
    // Detect button release (LOW to HIGH transition)
    if (buttonState == HIGH && lastState == LOW) {
        currentlyPressed = false;
        
        Serial.println("   ↑ Released");
        Serial.println();
        Serial.println("   Total presses: " + String(pressCount));
        Serial.println("   ─────────────────────────────────────");
        Serial.println();
        
        // LED OFF
        digitalWrite(BUILTIN_LED, LOW);
    }
    
    lastState = buttonState;
    delay(10);
}
