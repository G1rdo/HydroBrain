FROM python:3.11-slim
ENV PYTHONUNBUFFERED=1

WORKDIR /usr/src/sensor

# Install the Arduino CLI (Used to send the sensor ino to the sensor)
RUN apt-get update && apt-get install -y curl gcc ca-certificates python3-smbus && rm -rf /var/lib/apt/lists/*

# Install the python3 requirements
COPY requirements.txt ./
RUN pip install --no-cache-dir -r requirements.txt
RUN pip install --upgrade --force-reinstall adafruit-blinka adafruit-platformdetect
COPY . .

CMD [ "python", "./sensor.py" ]
