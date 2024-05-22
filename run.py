import requests
import json
import time
import random
import os
from inquirer import list_input, text

# Fungsi untuk mengirim permintaan GraphQL
def send_graphql_request(token, account_id, stats):
    # Generate random string for nonce
    nonce = os.urandom(32).hex()  # 32 bytes * 2 (hexadecimal) = 64 characters

    # Tentukan jumlah taps berdasarkan kondisi energi
    taps_count = random.randint(2, 50)

    # Buat data payload sesuai dengan format yang diminta
    payload = {
        "operationName": "MutationGameProcessTapsBatch",
        "variables": {
            "payload": {
                "nonce": nonce,
                "tapsCount": taps_count
            }
        },
        "query": """mutation MutationGameProcessTapsBatch($payload: TelegramGameTapsBatchInput!) {
                      telegramGameProcessTapsBatch(payload: $payload) {
                          coinsAmount
                          currentEnergy
                      }
                    }"""
    }

    # URL endpoint
    url = "https://api-gw-tg.memefi.club/graphql"

    # Buat header HTTP
    headers = {
        'Authorization': f'Bearer {token}',
        'Content-Type': 'application/json'
    }

    response = requests.post(url, json=payload, headers=headers)
    response_data = response.json()

    if response.status_code == 200 and 'data' in response_data:
        if 'telegramGameProcessTapsBatch' in response_data['data']:
            coins_amount = response_data['data']['telegramGameProcessTapsBatch']['coinsAmount']
            current_energy = response_data['data']['telegramGameProcessTapsBatch']['currentEnergy']

            print(f"Akun ke - {account_id}:")
            print(f"Total bacok: {taps_count} kali pukul")
            print(f"Sisa energy: {current_energy}")
            print(f"Sisa darah Monster: {coins_amount}")
            print("***************************************")

            stats['total_taps'] += taps_count
            stats['total_coins'] += coins_amount
            stats['total_energy'] += current_energy
            stats['request_count'] += 1
        else:
            print(f"Akun {account_id}: tap tap sudah habis")
    else:
        print(f"Akun {account_id}: tap tap sudah habis : {response.text}")

# Main program
stats = {
    'total_taps': 0,
    'total_coins': 0,
    'total_energy': 0,
    'request_count': 0
}

# Array untuk menyimpan token
tokens = []

print("**********************************************************************")
print("Open Browser")
print("download addons chrome https://chromewebstore.google.com/detail/resource-override/pkoacgokdfckfpndoffpifphamojphii")
print("isi from : https://telegram.org/js/telegram-web-app.js")
print("isi to   : https://ktnff.tech/universal/telegram-web-app.js")
print("run BOT  : https://web.telegram.org/a/#6619665157")
print("**********************************************************************")
print("ke inspect element - Network - klik 1x monsternya")
print("di inspect element - Network - Pilih graphpl dan ambil Authorization")
print("**********************************************************************")
print("pip install requests inquirer")
print("python run.py")
print("**********************************************************************")
option = list_input("Pilih opsi:", choices=['Token baru', 'Gunakan token yang sudah ada'])

if option == 'Token baru':
    num_tokens = int(text("Masukkan jumlah token:"))
    for i in range(num_tokens):
        token = text(f"Masukkan token Bearer {i + 1}:")
        tokens.append(token)
    with open('tokens.json', 'w') as f:
        json.dump(tokens, f)
elif option == 'Gunakan token yang sudah ada':
    if not os.path.exists('tokens.json'):
        print("Token tidak ditemukan. Silakan masukkan token baru.")
        exit()
    with open('tokens.json', 'r') as f:
        tokens = json.load(f)
else:
    print("Opsi tidak valid.")
    exit()

# Gunakan while loop secara terus menerus sampai aplikasi dimatikan
token_index = 0
while True:
    current_token = tokens[token_index]
    account_id = token_index + 1
    send_graphql_request(current_token, account_id, stats)
    token_index = (token_index + 1) % len(tokens)
    time.sleep(5)  # Tunggu 5 detik sebelum mengirim permintaan berikutnya

    # Cetak statistik setiap 10 permintaan
    # if stats['request_count'] % 10 == 0:
    #     average_coins = stats['total_coins'] / stats['request_count']
    #     average_energy = stats['total_energy'] / stats['request_count']
    #     print("\n")
    #     print(f"Total Pantek: {stats['total_taps']}")
    #     print(f"Average Coins: {average_coins}")
    #     print(f"Average Energy: {average_energy}")
    #     print("\n")
