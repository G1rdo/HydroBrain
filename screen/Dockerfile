FROM python:3.11-slim
ENV PYTHONUNBUFFERED=1

WORKDIR /usr/src/screen

# Install the Arduino CLI (Used to send the screen ino to the screen)
RUN apt-get update && apt-get install -y curl ca-certificates && rm -rf /var/lib/apt/lists/*

RUN ARCH=$(uname -m) && \
    case $ARCH in \
      aarch64) ARDUINO_ARCH="Linux_ARM64" ;; \
      armv7l)  ARDUINO_ARCH="Linux_ARMv7" ;; \
      x86_64)  ARDUINO_ARCH="Linux_64bit" ;; \
    esac && \
    curl -fsSL "https://downloads.arduino.cc/arduino-cli/arduino-cli_1.4.1_${ARDUINO_ARCH}.tar.gz" -o arduino-cli.tar.gz \
    && tar -xzf arduino-cli.tar.gz \
    && mv arduino-cli /usr/local/bin/ \
    && rm arduino-cli.tar.gz

RUN arduino-cli config init && \
    arduino-cli config set network.connection_timeout 600s && \
    arduino-cli config set board_manager.additional_urls https://raw.githubusercontent.com/espressif/arduino-esp32/gh-pages/package_esp32_index.json && \
    arduino-cli core update-index && \
    arduino-cli core install esp32:esp32

RUN arduino-cli lib install "TFT_eSPI" && \
    arduino-cli lib install "ArduinoJson@7.4.3"
COPY User_Setup.h /root/Arduino/libraries/TFT_eSPI/User_Setup.h

# Install the python3 requirements
COPY requirements.txt ./
RUN pip install --no-cache-dir -r requirements.txt

COPY . .

CMD [ "python", "./screen.py" ]
