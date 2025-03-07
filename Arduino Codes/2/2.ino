#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <DHT.h>

// WiFi Credentials
const char* ssid = "KingsMan";
const char* password = "#Iammhm#hw";
const char* serverName = "http://192.168.1.6/Cattle_Management_IOT_2.0/Main2.php";

// DHT11 Configuration
#define DHTPIN D2  // Data pin for DHT11
#define DHTTYPE DHT11
DHT dht(DHTPIN, DHTTYPE);

// Button and LED Pins
const int BUTTON_PIN = D5;
const int GREEN_LED = D6;   // Green LED (status = 1)
const int RED_LED = D7;     // Red LED (status = 0)

void setup() {
    Serial.begin(115200);
    dht.begin();

    pinMode(BUTTON_PIN, INPUT_PULLUP);
    pinMode(GREEN_LED, OUTPUT);
    pinMode(RED_LED, OUTPUT);
    
    WiFi.begin(ssid, password);
    Serial.println("Connecting to WiFi...");
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    Serial.println("\nConnected to WiFi!");
}

void loop() {
    static unsigned long lastReadMillis = 0;
    unsigned long currentMillis = millis();

    // Check button press to toggle status
    if (digitalRead(BUTTON_PIN) == LOW) {
        Serial.println("Button Pressed!!");
        toggleServerStatus();
        delay(500);
    }

    // Fetch status from server every 5 seconds
    if (currentMillis - lastReadMillis >= 5000) {
        lastReadMillis = currentMillis;
        int status = getServerStatus();
        Serial.print("Current Server Status: ");
        Serial.println(status);

        // Control LEDs based on status
        digitalWrite(GREEN_LED, status == 1 ? HIGH : LOW);
        digitalWrite(RED_LED, status == 0 ? HIGH : LOW);

        // Read and send DHT11 data if status = 1
        if (status == 1) {
            float temp = dht.readTemperature();
            float hum = dht.readHumidity();
            if (!isnan(temp) && !isnan(hum)) {
                sendDataToServer(temp, hum);
            } else {
                Serial.println("Failed to read from DHT sensor.");
            }
        }
    }
}

// Function to get current status from the server
int getServerStatus() {
    if (WiFi.status() == WL_CONNECTED) {
        WiFiClient wifiClient;
        HTTPClient http;
        http.begin(wifiClient, String(serverName) + "?getStatus=1");
        int httpResponseCode = http.GET();
        if (httpResponseCode > 0) {
            String response = http.getString();
            http.end();
            return response.toInt();
        }
        http.end();
    }
    return -1;
}

// Function to toggle status in the server
void toggleServerStatus() {
    if (WiFi.status() == WL_CONNECTED) {
        WiFiClient wifiClient;
        HTTPClient http;
        http.begin(wifiClient, String(serverName) + "?toggle=1");
        int httpResponseCode = http.GET();
        if (httpResponseCode > 0) {
            Serial.println("Server Status Updated!");
        }
        http.end();
    }
}

// Function to send temperature and humidity data
void sendDataToServer(float temp, float hum) {
    if (WiFi.status() == WL_CONNECTED) {
        WiFiClient wifiClient;
        HTTPClient http;
        String url = String(serverName) + "?temperature=" + String(temp, 2) + "&humidity=" + String(hum, 2);
        http.begin(wifiClient, url);
        int httpResponseCode = http.GET();
        if (httpResponseCode > 0) {
            Serial.println("Data Sent: " + http.getString());
        } else {
            Serial.println("Error Sending Data.");
        }
        http.end();
    }
}
