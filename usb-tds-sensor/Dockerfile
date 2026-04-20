FROM python:3.11-slim
ENV PYTHONUNBUFFERED=1

WORKDIR /usr/src/sensor

# Install the Arduino CLI (Used to send the sensor ino to the sensor)
RUN apt-get update && apt-get install -y curl ca-certificates && rm -rf /var/lib/apt/lists/*

RUN curl -fsSL https://downloads.arduino.cc/arduino-cli/arduino-cli_1.4.1_Linux_64bit.tar.gz -o arduino-cli.tar.gz \
    && tar -xzf arduino-cli.tar.gz \
    && mv arduino-cli /usr/local/bin/ \
    && rm arduino-cli.tar.gz

RUN arduino-cli config init && \
    arduino-cli config set network.connection_timeout 600s && \
    arduino-cli config set board_manager.additional_urls https://raw.githubusercontent.com/espressif/arduino-esp32/gh-pages/package_esp32_index.json && \
    arduino-cli core update-index && \
    arduino-cli core install arduino:avr

# Install the python3 requirements
COPY requirements.txt ./
RUN pip install --no-cache-dir -r requirements.txt

COPY . .

CMD [ "python", "./sensor.py" ]
