#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <SPI.h>
#include <MFRC522.h>
#include <WiFi.h>

#define SS_PIN 27
#define RST_PIN 5

MFRC522 rfid(SS_PIN, RST_PIN);
LiquidCrystal_I2C lcd(0x27, 16, 2);

const char* ssid = "Noname";
const char* password = "11111111";
const char* serverHost = "192.168.34.51";
const int serverPort = 80;

const byte predefinedUID1[] = {0x33, 0xBA, 0x9A, 0xF5};
const byte predefinedUID2[] = {0xB3, 0x76, 0x5B, 0xDD};

// Store last scan time for each UID
unsigned long lastScanTimes[2] = {0, 0}; // Array to store last scan times
const unsigned long SCAN_INTERVAL = 60000; // 1 minute interval in milliseconds

void setup() {
  Serial.begin(115200);
  SPI.begin();
  lcd.init();
  lcd.backlight();
  
  connectToWiFi();
  
  lcd.setCursor(2, 0); 
  lcd.print("Attendance");
  delay(1000);
  lcd.setCursor(1, 1); 
  lcd.print("System");
  delay(2000);

  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Please"); 
  lcd.setCursor(0, 1);
  lcd.print("Scan card");
  delay(1000);

  rfid.PCD_Init();
  Serial.println("Scan RFID card");
}

void loop() {
  if (!rfid.PICC_IsNewCardPresent()) {
    return;
  }

  if (!rfid.PICC_ReadCardSerial()) {
    return;
  }

  lcd.clear();
  Serial.print("Card UID: ");
  byte scannedUID[rfid.uid.size];

  // Read UID
  for (byte i = 0; i < rfid.uid.size; i++) {
    scannedUID[i] = rfid.uid.uidByte[i];
    Serial.print(rfid.uid.uidByte[i], HEX);
    Serial.print(" ");
  }
  Serial.println();

  // Check if UID matches predefined UIDs
  bool matchUID1 = compareUIDs(scannedUID, predefinedUID1, rfid.uid.size);
  bool matchUID2 = compareUIDs(scannedUID, predefinedUID2, rfid.uid.size);

  unsigned long currentMillis = millis(); // Current time

  if (matchUID1 || matchUID2) {        
    // Determine which UID was scanned
    int uidIndex = matchUID1 ? 0 : 1;

    if (currentMillis - lastScanTimes[uidIndex] < SCAN_INTERVAL) {
      // If scanned within 1 minute, show "Duplicate Card"
      lcd.setCursor(0, 0);
      lcd.print("Duplicate Card");
      Serial.println("Duplicate scan detected.");
    } else {
      // Update last scan time
      lastScanTimes[uidIndex] = currentMillis;

      // Show welcome message and send data to the server
      lcd.setCursor(0, 0);
      lcd.print("Welcome TO");
      lcd.setCursor(0, 1); 
      lcd.print("Nawajyoti");
      Serial.println("Present");

      String uidString = convertUIDToString(scannedUID, rfid.uid.size);
      sendDataToServer(uidString);
    }
  } else {
    lcd.print("Access Denied");
    Serial.println("Access Denied");
  } 

  delay(1000);

  lcd.clear();
  lcd.setCursor(0, 0); 
  lcd.print("Please");
  lcd.setCursor(0, 1);
  lcd.print("Scan card");
  delay(1000);

  rfid.PICC_HaltA();  
  rfid.PCD_StopCrypto1();
}

void connectToWiFi() {
  Serial.print("Connecting to WiFi...");
  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.print(".");
  }

  Serial.println("\nConnected to WiFi");
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());
}

void sendDataToServer(String uid) {
  WiFiClient client;
  String url = "/new_attendance.php?uid=" + uid;

  Serial.print("Connecting to ");
  Serial.print(serverHost);
  Serial.print(":");
  Serial.println(serverPort);

  if (client.connect(serverHost, serverPort)) {
    Serial.println("Connected to server");

    client.print(String("GET ") + url + " HTTP/1.1\r\n" +
                 "Host: " + serverHost + "\r\n" +
                 "Connection: close\r\n\r\n");

    while (client.connected() && !client.available()) {
      delay(10);
    }
    
    while (client.available()) {
      String line = client.readStringUntil('\r');
      Serial.print(line);
    }
  } else {
    Serial.println("Failed to connect to server");
  }

  client.stop();
}

bool compareUIDs(byte* scannedUID, const byte* predefinedUID, byte size) {
  for (byte i = 0; i < size; i++) {
    if (scannedUID[i] != predefinedUID[i]) {
      return false;
    }
  }
  return true;
}

String convertUIDToString(byte* uid, byte size) {
  String uidString = "";
  for (byte i = 0; i < size; i++) {
    uidString += String(uid[i], HEX);
    if (i < size - 1) uidString += ":";
  }
  return uidString;
}
