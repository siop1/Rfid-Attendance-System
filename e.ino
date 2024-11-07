#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <SPI.h>
#include <MFRC522.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

#define SS_PIN 27
#define RST_PIN 5

MFRC522 rfid(SS_PIN, RST_PIN);
LiquidCrystal_I2C lcd(0x27, 16, 2);

// Hardcoded Wi-Fi credentials
const char* currentSSID = "your_wifi_ssid";  // Replace with your Wi-Fi SSID
const char* currentPassword = "your_wifi_password";  // Replace with your Wi-Fi password

const char* serverHost = "192.168.34.51";  // Replace with your server's IP
const int serverPort = 80;

void setup() {
  Serial.begin(115200);
  SPI.begin();
  lcd.init();
  lcd.backlight();
  rfid.PCD_Init();

  lcd.setCursor(0, 0);
  lcd.print("Connecting to WiFi...");
  connectToWiFi();
}

void loop() {
  if (WiFi.status() != WL_CONNECTED) {
    connectToWiFi();
  }

  if (!rfid.PICC_IsNewCardPresent() || !rfid.PICC_ReadCardSerial()) {
    return;
  }

  String uid = getCardUID(rfid.uid.uidByte, rfid.uid.size);
  checkUser(uid);
  delay(1000);
}

String getCardUID(byte* uidByte, byte size) {
  String uid = "";
  for (byte i = 0; i < size; i++) {
    uid += String(uidByte[i], HEX);
  }
  return uid;
}

void connectToWiFi() {
  if (currentSSID != "" && currentPassword != "") {
    WiFi.begin(currentSSID, currentPassword);
    int attempt = 0;
    while (WiFi.status() != WL_CONNECTED && attempt < 10) {
      delay(1000);
      attempt++;
    }
    if (WiFi.status() != WL_CONNECTED) {
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("Wi-Fi failed!");
      delay(2000);
      while (true) { delay(1000); }
    } else {
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("Wi-Fi connected");
      delay(2000);
    }
  }
}

void checkUser(String uid) {
  HTTPClient http;
  String url = String("http://") + serverHost + "/check_user.php?uid=" + uid;
  http.begin(url);
  int httpCode = http.GET();
  String payload = http.getString();

  if (httpCode == 200) {
    DynamicJsonDocument doc(256);
    deserializeJson(doc, payload);
    String status = doc["status"].as<String>();

    if (status == "not_found") {
      // Display "Unknown Card" and the UID on LCD
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("Unknown Card");
      lcd.setCursor(0, 1);
      lcd.print("UID: " + uid);  // Display UID on the second line
      Serial.println("Unknown Card: " + uid);  // Print UID to Serial Monitor for logging
      delay(5000);  // Display for 5 seconds, adjust as needed
    } else {
      // User exists, proceed to attendance tracking
      sendAttendanceData(uid);
    }
  } else {
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Server Error");
    delay(1000);
  }
  http.end();
}

void sendAttendanceData(String uid) {
  HTTPClient http;
  
  // First, check if the user has already attended today
  String checkAttendanceUrl = String("http://") + serverHost + "/check_attendance.php?uid=" + uid;
  http.begin(checkAttendanceUrl);
  int httpCode = http.GET();
  String payload = http.getString();
  http.end();

  if (httpCode == 200) {
    DynamicJsonDocument doc(256);
    deserializeJson(doc, payload);
    String status = doc["status"].as<String>();

    if (status == "already_attended") {
      // User has already attended today
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("Already Attended");
      delay(1000);
    } else {
      // User hasn't attended, proceed to record attendance
      String attendanceUrl = String("http://") + serverHost + "/new_attendance.php?uid=" + uid;
      http.begin(attendanceUrl);
      httpCode = http.GET();
      
      if (httpCode == 200) {
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("Attendance Sent");
        delay(1000);
      } else {
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("Attendance Failed");
        delay(1000);
      }
      http.end();
    }
  } else {
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Server Error");
    delay(1000);
  }
}
