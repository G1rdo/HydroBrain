#include <TFT_eSPI.h>
#include <ArduinoJson.h>

TFT_eSPI tft = TFT_eSPI();
TFT_eSprite sprite = TFT_eSprite(&tft);

int PH = 6;
int incomingByte = 0;
int barLeftOffset = 40;
int barHorizontalDistance = 80;
// display: 240 by 320

// pointerPosition is how far down the status bar the pointer should be, and is a ratio from 0.0 to 1.0.
// 1.0 is all the way at the bottom of the bar, and the other arguments are the writeStatusBarRect arguments.
void drawBarWithPointer(float value, float pointerPosition, int barXStart, int barYStart, int barWidth, int barHeight, const std::string& rectType) {
  pointerPosition = constrain(pointerPosition, 0.0f, 1.0f);

  int pointerMargin = 13; // 10px from triangle + 3px gap
  int verticalMargin = 7; // Half of the size of the triange
  
  if (rectType == "midpoint") {
    sprite.createSprite(barWidth + pointerMargin + 10, barHeight + verticalMargin * 2 + 20); // + 20 px for the value
    sprite.fillSprite(TFT_WHITE);
  } else {
    sprite.createSprite(barWidth + pointerMargin + 10, barHeight + verticalMargin * 2);
    sprite.fillSprite(TFT_WHITE);
  }
  sprite.setTextFont(2);
  sprite.setTextColor(TFT_BLACK);
  sprite.setTextSize(2);

  // Draw the black border of status bar (with rounded corners)
  sprite.fillSmoothRoundRect(pointerMargin, verticalMargin, barWidth, barHeight, 3, TFT_BLACK);
  int bWidth = 3; // The black border width
  int colorXStart = pointerMargin + bWidth;
  int colorYStart = verticalMargin + bWidth;
  int colorWidth = barWidth - bWidth * 2;
  int colorHeight = barHeight - bWidth * 2;

  if (rectType == "midpoint") {
    int subsectionCount = 5; // Number of different subsection parts. i.e., the number of fillRects there are
    int subsectionHeight = colorHeight / subsectionCount;
    // Fill the status bar gradients in
    sprite.fillRectVGradient(colorXStart,
                          colorYStart,
                          colorWidth,
                          subsectionHeight,
                          TFT_RED, TFT_YELLOW);
    sprite.fillRectVGradient(colorXStart,
                          colorYStart + 1 * subsectionHeight,
                          colorWidth,
                          subsectionHeight,
                          TFT_YELLOW, TFT_GREEN);
    sprite.fillRect(colorXStart,
                 colorYStart + 2 * subsectionHeight,
                 colorWidth,
                 subsectionHeight,
                 TFT_GREEN);
    sprite.fillRectVGradient(colorXStart,
                          colorYStart + 3 * subsectionHeight,
                          colorWidth,
                          subsectionHeight,
                          TFT_GREEN, TFT_YELLOW);
    sprite.fillRectVGradient(colorXStart,
                          colorYStart + 4 * subsectionHeight,
                          colorWidth,
                          colorHeight - (subsectionCount - 1 ) * subsectionHeight,
                          TFT_YELLOW, TFT_RED);

    sprite.drawString(String(value, 1), 0, colorHeight + bWidth * 2 + 5);

    // Draw the units
    sprite.setTextFont(1);
    sprite.setTextColor(TFT_BLACK);
    sprite.setTextSize(1);
    if (barXStart == barLeftOffset) {
      // sprite.drawString("", 20, colorHeight + bWidth * 2 + 5);
    } else {
      sprite.drawString("mS", 42, colorHeight + bWidth * 2 + 18);
      sprite.drawString("/cm", 40, colorHeight + bWidth * 2 + 24);
    }

  } else {
    int subsectionCount = 3; // Number of different subsection parts. i.e., the number of fillRects there are
    int subsectionHeight = colorHeight / subsectionCount;
    // Fill the status bar gradients in
    sprite.fillRectVGradient(colorXStart,
                          colorYStart,
                          colorWidth,
                          subsectionHeight,
                          TFT_GREEN, TFT_YELLOW);
    sprite.fillRectVGradient(colorXStart,
                          colorYStart + 1 * subsectionHeight,
                          colorWidth,
                          subsectionHeight,
                          TFT_YELLOW, TFT_RED);
    sprite.fillRect(colorXStart,
                 colorYStart + 2 * subsectionHeight,
                 colorWidth,
                 subsectionHeight,
                 TFT_RED);
  }

  // Draw the pointer onto the sprite
  int centerX = pointerMargin - 3;
  // The vertical offset due to the pointerPosition
  int verticalOffset = (barHeight - 6) * pointerPosition;
  int centerY = verticalMargin + bWidth + (barHeight - 6) - verticalOffset;

  sprite.fillTriangle(centerX, centerY,
                   centerX - 10, centerY + 10,
                   centerX - 10, centerY - 10,
                   TFT_BLACK);
  sprite.fillRect(centerX + 3, centerY - 1, barWidth, 3, TFT_BLACK);

  sprite.pushSprite(barXStart - pointerMargin, barYStart - verticalMargin);
  sprite.deleteSprite();
}

void setup() {
  Serial.begin(115200);
  delay(500);

  tft.init();
  tft.setRotation(1);
  tft.fillScreen(TFT_WHITE);

  // tft.drawRect(10, 10, 300, 220, TFT_BLACK);

  tft.setTextFont(2);
  tft.setTextColor(TFT_BLACK);
  tft.setTextSize(2);

  // PH
  tft.drawString("PH", barLeftOffset + 5, 15);
  tft.drawString("#%", barLeftOffset + 5, 210);

  // Conductivity
  tft.drawString("EC", barLeftOffset + barHorizontalDistance + 5, 15);
  tft.drawString("#%", barLeftOffset + barHorizontalDistance + 5, 210);

  // Water Height (Boolean)
  tft.drawString("Full", barLeftOffset + barHorizontalDistance * 2, 15);
  tft.drawString("Low", barLeftOffset + barHorizontalDistance * 2, 210);
  // Serial.println("The Display Is Working");
}

void loop() {
  if (Serial.available() > 0) {
    String jsonString = Serial.readStringUntil('\n');
    
    JsonDocument doc;
    DeserializationError error = deserializeJson(doc, jsonString);
    
    if (error) {
      Serial.print("deserializeJson() failed: ");
      Serial.println(error.c_str());
      return;
    }
    float ph = doc["PH"];
    float ec = doc["EC"];
    float ph_status = doc["PH_Status"];
    float ec_status = doc["EC_Status"];
    float waterLevel_status = doc["WaterLevel_Status"];

    // PH
    drawBarWithPointer(ph, ph_status, barLeftOffset, 50, 40, 160, "midpoint");
    // Conductivity
    drawBarWithPointer(ec, ec_status, barLeftOffset + barHorizontalDistance, 50, 40, 160, "midpoint");
    // Water Height (Boolean)
    drawBarWithPointer(1.0, waterLevel_status, barLeftOffset + barHorizontalDistance * 2, 50, 40, 160, "top");
  }
}
