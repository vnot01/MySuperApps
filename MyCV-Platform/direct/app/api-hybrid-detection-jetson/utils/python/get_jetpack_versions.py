#!/usr/bin/env python3
"""
get_jetpack_versions.py
Scrapes the NVIDIA JetPack archive page to get JetPack and L4T version mapping.
"""

import requests
from bs4 import BeautifulSoup
import re
import sys
import os

def get_jetpack_l4t_versions():
    """
    Scrapes the NVIDIA JetPack archive page with corrected parsing logic.
    """
    URL = "https://developer.nvidia.com/embedded/jetpack-archive"
    headers = {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
    }
    
    try:
        response = requests.get(URL, headers=headers, timeout=15)
        response.raise_for_status()
    except requests.exceptions.RequestException as e:
        print(f"Error fetching the URL: {e}", file=sys.stderr)
        return None

    soup = BeautifulSoup(response.content, 'html.parser')
    
    versions = []
    
    main_list = soup.select_one('section.field-name-body div > ul')
    
    if not main_list:
        print("Could not find the main version list (<ul> tag). Page structure may have changed.", file=sys.stderr)
        return None

    for release_item in main_list.find_all('li', recursive=False):
        jetpack_link = release_item.find('a')
        if not jetpack_link:
            continue
            
        jetpack_version = jetpack_link.get_text(strip=True).replace('JetPack ', '').replace('Jetpack ', '').strip()

        sub_list = release_item.find('ul')
        if not sub_list:
            continue
            
        l4t_versions = set()
        
        for sub_item in sub_list.find_all('li'):
            full_text = sub_item.get_text()
            matches = re.findall(r'L4T\s+([\d\.]+)', full_text)
            
            if matches:
                for match in matches:
                    l4t_versions.add(match)
        
        if jetpack_version and l4t_versions:
            l4t_string = ", ".join(sorted(list(l4t_versions)))
            versions.append((jetpack_version, l4t_string))

    if not versions:
        print("Could not parse any versions. The list structure may have changed.", file=sys.stderr)
        return None
        
    return versions

def get_local_l4t_version():
    """
    Reads the local L4T version from /etc/nv_tegra_release using a robust regex.
    """
    try:
        with open('/etc/nv_tegra_release', 'r') as f:
            content = f.read()
            match = re.search(r'# R(\d+)[^,]*,\s*REVISION:\s*([\d\.]+)', content)
            if match:
                release_part = match.group(1)
                revision_part = match.group(2)
                return f"{release_part}.{revision_part}"
    except (FileNotFoundError, IndexError):
        return "N/A (not a Jetson system?)"
    except Exception as e:
        print(f"Could not read local L4T version: {e}", file=sys.stderr)
        return "N/A (read error)"
    return "N/A (unrecognized format)"

def get_jetpack_version():
    """
    Get JetPack version based on local L4T version.
    Returns tuple: (jetpack_version, l4t_version)
    """
    version_map = get_jetpack_l4t_versions()
    
    if not version_map:
        return "Unknown", "Unknown"
    
    local_l4t = get_local_l4t_version()
    
    if "N/A" in local_l4t:
        return "Unknown", local_l4t
    
    # Find matching JetPack version
    for jp, l4t_list_str in version_map:
        l4t_versions_in_row = [v.strip() for v in l4t_list_str.split(',')]
        
        for table_l4t in l4t_versions_in_row:
            if local_l4t.startswith(table_l4t):
                return jp, local_l4t
    
    return "Unknown", local_l4t

if __name__ == "__main__":
    version_map = get_jetpack_l4t_versions()
    
    if version_map:
        print("\n--- JetPack to L4T Version Map (Dynamically Scraped) ---")
        print(f"{'JetPack Version':<30} | {'L4T Version(s)':<25}")
        print("-" * 60)
        for jp, l4t in version_map:
            print(f"{jp:<30} | {l4t:<25}")
        print("-" * 60)

        local_l4t = get_local_l4t_version()
        print(f"\nYour local L4T version is: {local_l4t}\n")

        found_match = False
        if "N/A" not in local_l4t:
            # Iterasi melalui setiap baris di tabel yang sudah di-scrape
            for jp, l4t_list_str in version_map:
                # Pisahkan jika ada beberapa versi L4T dalam satu baris (misal: "28.2, 28.2.1")
                l4t_versions_in_row = [v.strip() for v in l4t_list_str.split(',')]
                
                # Iterasi melalui setiap versi L4T di baris tersebut
                for table_l4t in l4t_versions_in_row:
                    # === PERUBAHAN UTAMA DI SINI ===
                    # Gunakan startswith() untuk pencocokan yang fleksibel
                    # Ini akan mencocokkan '36.4.2' dengan '36.4'
                    if local_l4t.startswith(table_l4t):
                        print("==> Match Found!")
                        # Pesan yang lebih informatif
                        print(f"    Your L4T version ({local_l4t}) is compatible with the L4T {table_l4t} series, which corresponds to JetPack SDK {jp}.")
                        found_match = True
                        break # Hentikan pencarian di baris ini
                
                if found_match:
                    break # Hentikan pencarian di seluruh tabel

        if not found_match and "N/A" not in local_l4t:
             print("==> No exact match found in the table. You might be running a minor/developer preview release.")
