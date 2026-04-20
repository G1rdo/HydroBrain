#!/usr/bin/env python3

import subprocess
import time
import os

def start_adc_script(script_name):
  # Start the ADC (analog->digital converter) script and capture output
  adc_process = subprocess.Popen(
    ['python3', '-u', script_name],
    stdout=subprocess.PIPE,
    stderr=subprocess.PIPE
  )
  print("returning process")
  return adc_process

def read_process_analog_signal(process):
  # Reads the data from the child adc script
  while True:
    output = process.stdout.readline().decode().strip()
    if output == '':
      break # Exit when no output
    yield float(output) # Yield the output

def read_process_error(process):
  # Reads errors from stderr
  error_output = process.stderr.readline().decode().strip()
  if error_output:
    print(f"Error: {error_output}")

if __name__ == "__main__":
  print("Starting!")
  adcName = "ads1115"
  adc = start_adc_script(os.path.expanduser('~/hydrobrainpy/adcdrivers/ads1115.py'))
  
  # Read the analog signals and ensure they are numeric
  for analog_signal in read_process_analog_signal(adc):
    try:
      float_signal = float(analog_signal)
      print(f"Analog read: {analog_signal}")
    except ValueError:
      print(f"Recieved non-numeric output: {analog_signal}")
    time.sleep(0.5)
  read_process_error(adc)

  # Close the subprocesses
  adc.stdout.close()
  adc.stderr.close()
  adc.wait()
