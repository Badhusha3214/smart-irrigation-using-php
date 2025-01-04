#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

// WiFi credentials
const char* ssid = "Master";
const char* password = "123456789";

// Server details
const char* serverUrl = "http://192.168.254.73/abhi-mp/api/update_sensor_data.php";

// Update these pin definitions for ESP32
const int MOISTURE_SENSOR_PIN = 36;  // GPIO36/ADC1_CH0 (VP) - Analog input
const int WATER_PUMP_PIN = 4;        // GPIO4 - Digital output for pump
const int LED_PIN = 5;              // GPIO5 - Digital output for LED

// Constants
const int ADC_MAX = 4095;            // ESP32 has 12-bit ADC (0-4095)
const unsigned long SEND_INTERVAL = 5000;  // Send data every 5 seconds
const int WIFI_TIMEOUT = 10000;      // WiFi connection timeout in milliseconds

// Update these calibration values based on your sensor
const int AIR_VALUE = 4095;    // Value when sensor is in air (dry)
const int WATER_VALUE = 1800;  // Value when sensor is in water (wet)
const int READINGS = 10;       // Number of readings to average

// Variables
unsigned long lastSendTime = 0;
String sensorId = "SENSOR_001";
bool pumpStatus = false;
bool manualPumpControl = false;

void setup() {
  Serial.begin(115200);
  
  // Configure pins
  pinMode(MOISTURE_SENSOR_PIN, INPUT);
  pinMode(WATER_PUMP_PIN, OUTPUT);
  pinMode(LED_PIN, OUTPUT);
  
  digitalWrite(WATER_PUMP_PIN, LOW);
  digitalWrite(LED_PIN, LOW);
  
  // Connect to WiFi with timeout
  WiFi.mode(WIFI_STA);
  WiFi.setSleep(false);  // Disable WiFi sleep mode for better response times
  
  Serial.println("\nConnecting to WiFi...");
  WiFi.begin(ssid, password);
  
  unsigned long startAttemptTime = millis();
  while (WiFi.status() != WL_CONNECTED && 
         millis() - startAttemptTime < WIFI_TIMEOUT) {
    delay(100);
    Serial.print(".");
  }
  
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("\nFailed to connect to WiFi. Restarting...");
    ESP.restart();
  }
  
  Serial.println("\nConnected to WiFi");
  Serial.print("IP Address: ");
  Serial.println(WiFi.localIP());
}

void loop() {
  if (WiFi.status() == WL_CONNECTED) {
    unsigned long currentTime = millis();
    
    if (currentTime - lastSendTime >= SEND_INTERVAL) {
      int moistureLevel = readMoistureSensor();
      sendSensorData(moistureLevel);
      lastSendTime = currentTime;
    }
  } else {
    Serial.println("WiFi Connection lost! Attempting to reconnect...");
    WiFi.disconnect();
    WiFi.reconnect();
    delay(5000);  // Wait before next attempt
  }
}

int readMoistureSensor() {
  long sum = 0;
  int rawValue;
  
  // Take multiple readings for stability
  for(int i = 0; i < READINGS; i++) {
    rawValue = analogRead(MOISTURE_SENSOR_PIN);
    sum += rawValue;
    delay(10);
  }
  
  // Calculate average
  rawValue = sum / READINGS;
  
  // Debug raw value
  Serial.print("Raw ADC Value: ");
  Serial.println(rawValue);
  
  // If reading is 0, sensor might be disconnected
  if (rawValue == 0) {
    Serial.println("WARNING: Sensor might be disconnected!");
    return -1;
  }
  
  // Map the value to 0-100 range (inverted because higher ADC = drier soil)
  int moisturePercent = map(constrain(rawValue, WATER_VALUE, AIR_VALUE),
                          AIR_VALUE, WATER_VALUE,
                          0, 100);
  
  // Print detailed debug info
  Serial.print("Moisture: ");
  Serial.print(moisturePercent);
  Serial.print("% (Raw: ");
  Serial.print(rawValue);
  Serial.println(")");
  
  return moisturePercent;
}

void sendSensorData(int moistureLevel) {
  // Don't send data if sensor reading is invalid
  if (moistureLevel < 0) {
    Serial.println("Skipping data send due to invalid reading");
    return;
  }
  
  HTTPClient http;
  
  http.setTimeout(10000);
  http.begin(serverUrl);
  http.addHeader("Content-Type", "application/json");
  
  StaticJsonDocument<200> doc;
  doc["sensor_id"] = sensorId;
  doc["moisture_level"] = moistureLevel;
  
  String jsonString;
  serializeJson(doc, jsonString);
  
  int httpResponseCode = http.POST(jsonString);
  
  if (httpResponseCode > 0) {
    String response = http.getString();
    Serial.println("Server response: " + response);
    
    DynamicJsonDocument responseDoc(200);
    DeserializationError error = deserializeJson(responseDoc, response);
    
    if (!error) {
      if (responseDoc.containsKey("pump_control")) {
        // Handle manual pump control from web interface
        manualPumpControl = responseDoc["pump_control"].as<bool>();
        activatePump(manualPumpControl);
      } else if (!manualPumpControl) {
        // Adjusted moisture thresholds
        if (moistureLevel < 20) {         // Very dry
          activatePump(true);
        } else if (moistureLevel > 60) {  // Sufficiently moist
          activatePump(false);
        }
      }
    }
  } else {
    Serial.print("Error sending data. Error code: ");
    Serial.println(httpResponseCode);
  }
  
  http.end();
}

void activatePump(bool activate) {
  digitalWrite(WATER_PUMP_PIN, activate);  // Changed to direct control
  digitalWrite(LED_PIN, activate);         // LED indicates pump status
  pumpStatus = activate;
  Serial.print("Pump and LED status changed to: ");
  Serial.println(activate ? "ON" : "OFF");
}

void blinkLED() {
  digitalWrite(LED_PIN, HIGH);
  delay(100);
  digitalWrite(LED_PIN, LOW);
}