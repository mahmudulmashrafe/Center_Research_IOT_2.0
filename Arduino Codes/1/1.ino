#include <ESP8266WiFi.h> 
#include <ESP8266HTTPClient.h>
#include <ESP8266WebServer.h>

// WiFi Credentials
const char* ssid = "KingsMan";
const char* password = "#Iammhm#hw";
const char* serverName = "http://192.168.1.6/Cattle_Management_IOT_2.0/Main.php";

// Ultrasonic Sensor Pins
const int TRIG_PIN = D1;   
const int ECHO_PIN = D2;   
const int BUTTON_PIN = D5;  // Button to manually trigger measurement

// LED Pins
const int GREEN_LED = D6;   // Green LED (status = 1)
const int RED_LED = D7;     // Red LED (status = 0)
const int CONTROL_LED = D4; // LED controlled via server status

int lastServerStatus = -1;  // Stores last known status
bool lastButtonState = HIGH; // Stores last button state

void setup() {
    Serial.begin(115200);
    delay(1000);

    pinMode(TRIG_PIN, OUTPUT);
    pinMode(ECHO_PIN, INPUT);
    pinMode(BUTTON_PIN, INPUT_PULLUP);
    pinMode(GREEN_LED, OUTPUT);
    pinMode(RED_LED, OUTPUT);
    pinMode(CONTROL_LED, OUTPUT);
    digitalWrite(GREEN_LED, LOW);
    digitalWrite(RED_LED, HIGH);

    Serial.println("Connecting to WiFi...");
    WiFi.begin(ssid, password);
    
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print("...");
    }

    Serial.println("\nConnected to WiFi!");
    Serial.print("IP Address: ");
    Serial.println(WiFi.localIP());
}

void loop() {
    static unsigned long lastReadingMillis = 0; 
    unsigned long currentMillis = millis();

    // Button press detection for toggling server status
    int buttonState = digitalRead(BUTTON_PIN);
    if (buttonState == LOW && lastButtonState == HIGH) { // Button pressed
        Serial.println("Button Pressed!! Toggling status...");
        toggleServerStatus();
        delay(300); // Debounce delay
    }
    lastButtonState = buttonState;

    // Fetch and update status only when it changes
    int serverStatus = getServerStatus();
    if (serverStatus != lastServerStatus) {
        Serial.print("Status Changed: ");
        Serial.println(serverStatus);
        controlLEDs(serverStatus);
        lastServerStatus = serverStatus;
    }

    // Take Distance Reading Every 10 Seconds
    if (currentMillis - lastReadingMillis >= 10000) {
        lastReadingMillis = currentMillis;
        takeAndSendReading();
    }
}

// Function to get status from server
int getServerStatus() {
    if (WiFi.status() == WL_CONNECTED) {
        WiFiClient wifiClient;
        HTTPClient http;
        http.begin(wifiClient, String(serverName) + "?getStatus=1");
        int httpResponseCode = http.GET();

        if (httpResponseCode > 0) {
            String response = http.getString();
            http.end();
            return response.toInt(); // Convert server response to integer
        } else {
            Serial.printf("Failed to connect to server. HTTP Error: %d\n", httpResponseCode);
        }
        http.end();
    } else {
        Serial.println("WiFi Disconnected!");
    }
    return -1; 
}

// Function to toggle server status
void toggleServerStatus() {
    if (WiFi.status() == WL_CONNECTED) {
        WiFiClient wifiClient;
        HTTPClient http;
        http.begin(wifiClient, String(serverName) + "?toggle=1");
        int httpResponseCode = http.GET();

        if (httpResponseCode > 0) {
            String response = http.getString();
            Serial.println("Server Response: " + response);
        } else {
            Serial.printf("HTTP Error: %d\n", httpResponseCode);
        }
        http.end();
    } else {
        Serial.println("WiFi Disconnected!");
    }
}

// Function to take ultrasonic distance reading
void takeAndSendReading() {
    float distance = getDistance();
    if (distance != -1) {
        Serial.print("Distance: ");
        Serial.print(distance);
        Serial.println(" cm");

        sendDataToServer(distance);
    } else {
        Serial.println("Sensor Timeout or Out of Range");
    }
}

// Function to get distance from ultrasonic sensor
float getDistance() {
    digitalWrite(TRIG_PIN, LOW);
    delayMicroseconds(2);
    digitalWrite(TRIG_PIN, HIGH);
    delayMicroseconds(10);
    digitalWrite(TRIG_PIN, LOW);

    long duration = pulseIn(ECHO_PIN, HIGH);
    if (duration == 0) return -1; // No response

    float distance = duration * 0.034 / 2;
    return distance;
}

// Function to update LEDs based on status
void controlLEDs(int status) {
    if (status == 1) {
        digitalWrite(GREEN_LED, HIGH);
        digitalWrite(RED_LED, LOW);
    } else {
        digitalWrite(GREEN_LED, LOW);
        digitalWrite(RED_LED, HIGH);
    }
}

// Function to send distance data to server
void sendDataToServer(float distance) {
    if (WiFi.status() == WL_CONNECTED) {
        WiFiClient wifiClient;
        HTTPClient http;
        http.begin(wifiClient, String(serverName) + "?distance=" + String(distance, 2));
        int httpResponseCode = http.GET();

        if (httpResponseCode > 0) {
            Serial.println("Server Response: " + http.getString());
        } else {
            Serial.printf("HTTP Error: %d\n", httpResponseCode);
        }
        http.end();
    } else {
        Serial.println("WiFi Disconnected!");
    }
}
