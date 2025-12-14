# Profile Component CSS Guide

Modern, clean styling for profile components following the sleek design aesthetic.

## Design Principles
- **Clean & Modern**: Subtle borders, light backgrounds, rounded corners
- **Typography**: Inter font family, uppercase labels with wide tracking
- **Colors**: Slate palette (50, 100, 200, etc.) for subtle, professional look
- **Spacing**: Consistent padding and margins for visual harmony

## CSS Classes Reference

### 1. Cards & Containers

#### `.profile-component-card`
Main container for profile sections
```html
<div class="profile-component-card">
    <!-- Content here -->
</div>
```
- White background with subtle shadow
- Rounded corners (14px)
- 1px border in slate-200

#### `.profile-card-header`
Header section with title and actions
```html
<div class="profile-card-header">
    <div>
        <p class="profile-card-meta">STUDENT PROFILE</p>
        <p class="profile-card-title">Personal Information</p>
        <p class="profile-card-subtitle">STUDENT</p>
    </div>
    <div class="profile-card-actions">
        <button class="gear-button"><i class="fa fa-gear"></i></button>
    </div>
</div>
```

### 2. Display Fields (Read-Only)

#### `.profile-info-field`
Individual field display with label and value
```html
<div class="profile-info-field">
    <p class="profile-info-field-label">USERNAME</p>
    <p class="profile-info-field-value">john.doe</p>
</div>
```
- Light slate background (#f8fafc)
- Rounded borders
- Uppercase labels with wide letter-spacing (0.3em)

#### `.profile-field-grid`
2-column responsive grid for fields
```html
<div class="profile-field-grid">
    <div class="profile-info-field">...</div>
    <div class="profile-info-field">...</div>
</div>
```

### 3. Forms (Create/Edit)

#### `.profile-form-card`
Form container
```html
<div class="profile-form-card">
    <form>...</form>
</div>
```

#### `.form-group`
Individual form field wrapper
```html
<div class="form-group">
    <label class="form-label">Email Address</label>
    <input type="email" class="form-input" />
</div>
```

#### Form Input Classes
- `.form-input` - Text inputs
- `.form-select` - Dropdowns with custom arrow
- `.form-label` - Uppercase, wide-tracked labels

#### `.form-row`
2-column layout for side-by-side fields
```html
<div class="form-row">
    <div class="form-col">
        <label class="form-label">Year Start</label>
        <input type="number" class="form-input" />
    </div>
    <div class="form-col">
        <label class="form-label">Year End</label>
        <input type="number" class="form-input" />
    </div>
</div>
```

#### `.form-actions`
Button container at form bottom
```html
<div class="form-actions">
    <button type="submit" class="btn btn-primary">Save</button>
    <button type="button" class="btn btn-outline">Cancel</button>
</div>
```

### 4. Tables

#### `.profile-table`
Standard data table
```html
<table class="profile-table">
    <thead>
        <tr>
            <th>School</th>
            <th>Year</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><a href="#" class="profile-link">School Name</a></td>
            <td>2020</td>
        </tr>
    </tbody>
</table>
```
- Uppercase headers with subtle color
- Alternating row backgrounds
- Hover effects

#### Table Variants
- `.table-vertical` - Standard vertical table (default)
- `.table-horizontal` - Label-value pairs (horizontal layout)
- `.summary-table` - Summary information display

### 5. Record Cards (Grid Display)

#### `.profile-records-grid`
Grid layout for record cards
```html
<div class="profile-records-grid">
    <a href="#" class="profile-record-card">
        <p class="profile-record-title">Bachelor of Science</p>
        <p class="profile-record-subtitle">University of Manila</p>
        <p class="profile-record-meta">2020</p>
    </a>
</div>
```
- 2-column responsive grid
- Hover effects on cards
- Clean card styling with subtle borders

### 6. Buttons

#### Button Classes
```html
<button class="btn btn-primary">Save</button>
<button class="btn btn-outline">Cancel</button>
<button class="btn btn-danger">Delete</button>
<button class="btn btn-ghost">Action</button>
<button class="btn btn-sm">Small</button>
```

- `.btn-primary` - Dark slate background (main action)
- `.btn-outline` - White with border (secondary)
- `.btn-danger` - Red text/border (destructive)
- `.btn-ghost` - Transparent (subtle actions)
- `.btn-sm` - Smaller size variant

#### `.gear-button`
Gear icon button for card headers
```html
<button class="gear-button">
    <i class="fa fa-gear"></i>
</button>
```

### 7. Links

#### `.profile-link`
Styled links within tables/content
```html
<a href="#" class="profile-link">View Details</a>
```
- Blue color (#3b82f6)
- Underline on hover

#### `.small-link`
Small helper links
```html
<a href="#" class="small-link">Add new option</a>
```

### 8. Empty States

#### `.profile-empty-state`
No data message
```html
<div class="profile-empty-state">
    No records found.
</div>
```

## Color Palette

- **Backgrounds**: 
  - Card: `#ffffff`
  - Field: `#f8fafc` (slate-50)
  - Hover: `#f1f5f9` (slate-100)

- **Borders**: 
  - Default: `#e2e8f0` (slate-200)
  - Light: `#f1f5f9` (slate-100)
  - Medium: `#cbd5e1` (slate-300)

- **Text**: 
  - Primary: `#0f172a` (slate-900)
  - Secondary: `#64748b` (slate-500)
  - Muted: `#94a3b8` (slate-400)

- **Accent**: 
  - Link: `#3b82f6` (blue-500)
  - Button: `#475569` (slate-600)

## Typography

- **Font Family**: Inter, Poppins, system-ui
- **Label Style**: 
  - Size: 0.75rem (12px)
  - Weight: 600
  - Transform: uppercase
  - Tracking: 0.05em - 0.3em (wide)
- **Value Style**:
  - Size: 0.875rem (14px)
  - Weight: 500-600
  - Color: slate-900

## Usage Examples

### Complete Account Information Card
```html
<div class="profile-component-card">
    <div class="profile-card-header">
        <div>
            <p class="profile-card-title">Account Information</p>
        </div>
        <div class="profile-card-actions">
            <button class="gear-button"><i class="fa fa-gear"></i></button>
        </div>
    </div>
    
    <div class="profile-field-grid">
        <div class="profile-info-field">
            <p class="profile-info-field-label">USERNAME</p>
            <p class="profile-info-field-value">john.doe</p>
        </div>
        <div class="profile-info-field">
            <p class="profile-info-field-label">EMAIL</p>
            <p class="profile-info-field-value">john@example.com</p>
        </div>
    </div>
</div>
```

### Complete Form
```html
<div class="profile-form-card">
    <form>
        <div class="form-group">
            <label class="form-label">School Name</label>
            <select class="form-select">
                <option>Select...</option>
            </select>
        </div>
        
        <div class="form-row">
            <div class="form-col">
                <label class="form-label">Year Start</label>
                <input type="number" class="form-input" />
            </div>
            <div class="form-col">
                <label class="form-label">Year End</label>
                <input type="number" class="form-input" />
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="#" class="small-link">Cancel</a>
        </div>
    </form>
</div>
```

### Complete Table
```html
<div class="profile-component-card">
    <div class="profile-card-header">
        <div>
            <p class="profile-card-title">Educational Records</p>
        </div>
        <div class="profile-card-actions">
            <a href="#" class="gear-button"><i class="fa fa-plus"></i></a>
        </div>
    </div>
    
    <table class="profile-table">
        <thead>
            <tr>
                <th>School</th>
                <th>Year</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><a href="#" class="profile-link">University Name</a></td>
                <td>2020</td>
            </tr>
        </tbody>
    </table>
</div>
```

## Notes

- All components are fully responsive
- Focus states include subtle shadows
- Hover effects provide visual feedback
- Consistent spacing throughout (0.75rem, 1rem, 1.25rem)
- Rounded corners range from 8px (fields) to 12px (cards)
