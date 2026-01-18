# Understanding Your Storage Problem

## What's Happening Now

```
┌─────────────────────────────────────────────────────────────┐
│ User uploads profile image                                   │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ Laravel tries to save to: public_html/storage/profile-images│
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ Permission check: Can web server write here?                 │
└────────────────────┬────────────────────────────────────────┘
                     │
         ┌───────────┴───────────┐
         │                       │
         ▼                       ▼
    ┌─────────┐            ┌─────────┐
    │ 755     │            │ 775     │
    │ (wrong) │            │ (right) │
    └────┬────┘            └────┬────┘
         │                      │
         ▼                      ▼
    ┌─────────┐            ┌─────────┐
    │ ❌ DENIED│            │ ✅ SAVED │
    └─────────┘            └─────────┘
         │                      │
         ▼                      ▼
  Image doesn't save      Image saves and displays!
```

## Permission Breakdown

### 755 (What you have now - WRONG)

```
7   5   5
│   │   │
│   │   └── Others: r-x (read + execute only)
│   └────── Group:  r-x (read + execute only) ← Web server is here!
└────────── Owner:  rwx (read + write + execute)
```

**Result:** Web server CANNOT write files ❌

### 775 (What you need - CORRECT)

```
7   7   5
│   │   │
│   │   └── Others: r-x (read + execute only)
│   └────── Group:  rwx (read + write + execute) ← Web server CAN write!
└────────── Owner:  rwx (read + write + execute)
```

**Result:** Web server CAN write files ✅

## Why Old Images Show But New Ones Don't

### Old Images (Copied manually)

```
You copied files → Files exist with 755 → Web server can READ them ✅
```

### New Images (Uploaded through Laravel)

```
User uploads → Laravel tries to WRITE → Permission 755 blocks it ❌
```

## The Fix (One Command)

```bash
chmod -R 775 public_html/storage
```

This changes:
- `drwxr-xr-x` (755) ❌
- To: `drwxrwxr-x` (775) ✅

Notice the extra `w` in the middle? That's what allows uploads!

## Visual Comparison

### Before Fix (755)

```
$ ls -la public_html/storage/
drwxr-xr-x  linkadic linkadic  profile-images/
     ↑
     No 'w' here - web server can't write!
```

### After Fix (775)

```
$ ls -la public_html/storage/
drwxrwxr-x  linkadic linkadic  profile-images/
     ↑
     'w' here - web server CAN write!
```

## Real-World Example

### Scenario 1: User uploads profile image

**With 755 (wrong):**
```
1. User selects image
2. Livewire uploads to temp
3. Laravel tries: $image->store('profile-images', 'public')
4. Server says: "Permission denied" ❌
5. Upload fails silently
6. User sees old image or no image
```

**With 775 (correct):**
```
1. User selects image
2. Livewire uploads to temp
3. Laravel tries: $image->store('profile-images', 'public')
4. Server says: "OK, saved!" ✅
5. File saved to: public_html/storage/profile-images/abc123.jpg
6. User sees new image at: https://linkadi.co.tz/storage/profile-images/abc123.jpg
```

## How to Verify It's Fixed

### Test 1: Check permissions
```bash
ls -ld public_html/storage/
```

**Look for:** `drwxrwxr-x` (the middle `rwx` is key!)

### Test 2: Try creating a file
```bash
touch public_html/storage/test.txt
```

**If this works:** Permissions are correct ✅  
**If this fails:** Permissions still wrong ❌

### Test 3: Upload through website
1. Go to profile builder
2. Upload new profile image
3. Click save
4. Image should display immediately ✅

## Common Questions

### Q: Why not just use 777?

**A:** 777 gives EVERYONE write access (security risk). 775 gives only owner and group (safer).

### Q: Will this break anything?

**A:** No! 775 is the standard permission for web-writable directories.

### Q: Do I need to do this every time?

**A:** No! Once you set 775, it stays that way (unless you manually change it).

### Q: What if 775 doesn't work?

**A:** Contact your hosting provider - there may be server-level restrictions (rare on cPanel).

## Summary

**Your Problem:**
- Old images show ✅ (you copied them manually)
- New uploads don't save ❌ (web server can't write)

**The Cause:**
- Permission 755 blocks web server from writing

**The Solution:**
- Change to 775 to allow web server to write

**The Fix:**
```bash
chmod -R 775 public_html/storage
```

That's it! 🎉
