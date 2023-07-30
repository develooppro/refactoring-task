#!/bin/bash

cp .env.example .env

echo "Enter the api key for the exchange rates api (you can get it from https://exchangeratesapi.io/documentation/): "
read -rs api_key

sed -i '' "s/EXCHANGE_RATES_API_KEY=.*/EXCHANGE_RATES_API_KEY=$api_key/" .env