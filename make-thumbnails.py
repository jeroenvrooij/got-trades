from PIL import Image
import os

# Updated base folder and thumbnails folder
base_folder = 'assets/images/card_images/images'
thumbnails_root = 'assets/images/card_images/thumbnails'  # moved one level up
target_height = 400  # adjust as needed

# Create thumbnails root folder if missing
os.makedirs(thumbnails_root, exist_ok=True)

for root, dirs, files in os.walk(base_folder):
    # Skip processing the thumbnails folder itself (safety)
    if thumbnails_root in root:
        continue

    # Compute relative path from base_folder
    rel_path = os.path.relpath(root, base_folder)

    # Create matching subfolder under thumbnails_root
    thumbnail_folder = os.path.join(thumbnails_root, rel_path)
    os.makedirs(thumbnail_folder, exist_ok=True)

    for filename in files:
        if filename.lower().endswith(('.png', '.jpg', '.jpeg', '.webp')):
            img_path = os.path.join(root, filename)

            # Open and resize image
            img = Image.open(img_path)
            aspect_ratio = img.width / img.height
            new_width = int(target_height * aspect_ratio)
            img = img.resize((new_width, target_height), Image.LANCZOS)

            # Build output filename: same name but with .webp extension
            name_without_ext = os.path.splitext(filename)[0]
            output_filename = name_without_ext + '.webp'
            output_path = os.path.join(thumbnail_folder, output_filename)

            # Save as WebP
            img.save(output_path, 'WEBP', optimize=True, quality=85)

            print(f"Saved thumbnail: {output_path}")
