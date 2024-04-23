import json
import os
import csv
import gzip
import shutil
import requests
from collections import defaultdict

# Function to download and extract the file
def download_and_extract(url, destination_folder):
    # Download the file
    file_path = os.path.join(destination_folder, "title.episode.tsv.gz")
    with open(file_path, "wb") as f:
        response = requests.get(url)
        f.write(response.content)

    # Extract the file
    with gzip.open(file_path, 'rb') as f_in, open(os.path.join(destination_folder, "data.tsv"), 'wb') as f_out:
        shutil.copyfileobj(f_in, f_out)

    # Delete the compressed file
    os.remove(file_path)

# Replace placeholders with your actual password and database name

# Construct the connection URI

# Create a new client and connect to the server

# Send a ping to confirm a successful connection


try:
    # Root directory
    root_dir = os.path.dirname(os.path.abspath(__file__))
    data_folder = os.path.join(root_dir, "../data")

    # Download and extract the file
    download_and_extract("https://datasets.imdbws.com/title.episode.tsv.gz", data_folder)

    # Path to the TSV file
    tsv_file = os.path.join(data_folder, "data.tsv")

    # Dictionary to store counts of unique IDs
    id_counts = defaultdict(int)

    # Read the TSV file
    with open(tsv_file, mode='r', encoding='utf-8') as file:
        reader = csv.reader(file, delimiter='\t')
        # Skip header if exists
        next(reader, None)
        
        # Iterate over each row in the TSV file
        for row in reader:
            id_counts[row[1]] += 1 

    # Convert defaultdict to a regular dictionary and sort by values
    sorted_id_counts = dict(sorted(id_counts.items(), key=lambda item: item[1], reverse=True))
    json_data = json.dumps(sorted_id_counts)
    
    # Save json_data to a JSON file named 'episodes_data.json' in the data folder
    json_file_path = os.path.join(data_folder, "episodes_data.json")
    with open(json_file_path, 'w') as json_file:
        json_file.write(json_data)

    # MongoDB connection parameters
    db_name = "Untitled_Project"
    collection_name = "episode_count"

    # Get the database and collection

    # Check if a document already exists in the collection

finally:
    # Close the MongoDB connection

    # Delete the extracted files
    extracted_files = os.listdir(data_folder)
    for file_name in extracted_files:
        if file_name.endswith(".tsv"):
            file_path = os.path.join(data_folder, file_name)
            os.remove(file_path)
    print("Extracted files deleted.")
