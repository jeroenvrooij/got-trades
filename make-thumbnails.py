from PIL import Image, UnidentifiedImageError
import os

# Updated base folder and thumbnails folder
base_folder = 'assets/images/card_images/images'
thumbnails_root = 'assets/images/card_images/thumbnails'  # moved one level up
target_height = 400  # adjust as needed

# Collect errors for a final report
skipped_files = []

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

            # Build output filename: same name but with .webp extension
            name_without_ext = os.path.splitext(filename)[0]
            output_filename = name_without_ext + '.webp'
            output_path = os.path.join(thumbnail_folder, output_filename)

            # Skip if thumbnail already exists
            if os.path.exists(output_path):
                print(f"Skipped (already exists): {output_path}")
                continue

            try:
                # Open and resize image
                img = Image.open(img_path)
                aspect_ratio = img.width / img.height
                new_width = int(target_height * aspect_ratio)
                img = img.resize((new_width, target_height), Image.LANCZOS)

                # Save as WebP
                img.save(output_path, 'WEBP', optimize=True, quality=85)

                print(f"Saved thumbnail: {output_path}")

            except UnidentifiedImageError:
                skipped_files.append((img_path, "UnidentifiedImageError"))
            except Exception as e:
                skipped_files.append((img_path, str(e)))
                
# --- Final report ---
print("\n=== Thumbnail Generation Complete ===")
if skipped_files:
    print("The following files were skipped or failed:")
    for path, reason in skipped_files:
        print(f" - {path}: {reason}")
else:
    print("All images processed successfully!")