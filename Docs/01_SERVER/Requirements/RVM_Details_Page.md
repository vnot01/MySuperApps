# RVM Details Page - Server Documentation

## Overview
RVM Details Page adalah halaman lengkap untuk melihat semua informasi detail dari Reverse Vending Machine (RVM) yang dipilih. Halaman ini bukan modal atau popup, melainkan halaman penuh dengan navigasi.

## Features

### 1. Navigation
- **Back Button**: Tombol kembali ke dashboard
- **Breadcrumb**: RVM Details sebagai judul halaman

### 2. Action Buttons
- **Enter Maintenance**: Mengubah status RVM ke maintenance mode
- **Enter Playground**: Fitur playground untuk testing (future implementation)

### 3. Information Sections

#### 3.1 Basic Information
- RVM Name
- Location
- Address
- IP Address
- Status (dengan badge berwarna)
- Latitude & Longitude

#### 3.2 Capacity & Load Information
- Total Capacity
- Current Load
- Usage Percentage (dengan progress bar)
- Over capacity warning

#### 3.3 API Information
- API Key (dengan tombol copy)
- API Key Expiration Date

#### 3.4 Status Indicators
- Operational Status (Active/Inactive/Maintenance/Error)
- Connection Status (Connected/Disconnected dengan dot indicator)
- API Status (Valid/Invalid dengan dot indicator)

#### 3.5 Last Activity
- Last Ping (dengan priority logic)
- Last Connection Check
- Last API Check

#### 3.6 System Information
- Created At
- Updated At
- RVM ID

## Technical Implementation

### Backend (Laravel)

#### Routes
```php
Route::get('/rvms/{rvm}', [RvmController::class, 'show'])->name('rvms.show');
```

#### Controller Method
```php
public function show(ReverseVendingMachine $rvm)
{
    return Inertia::render('Rvms/Show', [
        'rvm' => [
            'id' => $rvm->id,
            'name' => $rvm->name,
            'location' => $rvm->location,
            // ... all RVM data
        ]
    ]);
}
```

### Frontend (Vue.js)

#### Component Structure
- **Show.vue**: Main component untuk RVM details
- **Responsive Layout**: Grid layout dengan sidebar
- **Status Indicators**: Visual indicators dengan colors
- **Action Buttons**: Maintenance dan Playground actions

#### Key Functions
- `formatDate()`: Format tanggal Indonesia
- `formatTimeAgo()`: Format waktu relatif
- `getLastPingInfo()`: Priority logic untuk last ping
- `getStatusBadgeClass()`: CSS classes untuk status badges
- `copyApiKey()`: Copy API key ke clipboard
- `enterMaintenance()`: Update status ke maintenance
- `enterPlayground()`: Future playground functionality
- `toggleEditMode()`: Toggle between view and edit modes
- `saveChanges()`: Save RVM changes to database

## Data Flow

### 1. Navigation Flow
```
Dashboard → Action Menu → Lihat Details → RVM Details Page
```

### 2. Data Loading
```
User clicks "Lihat Details" → 
RvmController@show → 
Database query → 
Format data → 
Inertia render → 
Vue component display
```

### 3. Action Flow
```
User clicks "Enter Maintenance" → 
Confirmation dialog → 
API call to update status → 
Page reload → 
Updated status display
```

### 4. Edit Mode Flow
```
User clicks "Edit" → 
Form fields become editable → 
User modifies data → 
User clicks "Finish" → 
API call to update RVM → 
Success message → 
Form returns to view mode
```

## Status Management

### Operational Status
- **Active**: RVM beroperasi normal
- **Inactive**: RVM tidak aktif
- **Maintenance**: RVM dalam mode maintenance
- **Error**: RVM mengalami error

### Connection Status
- **Connected**: Ping ke IP berhasil
- **Disconnected**: Ping ke IP gagal atau tidak ada IP

### API Status
- **Valid**: Health endpoint responsif
- **Invalid**: Health endpoint tidak responsif atau tidak ada IP

## Security Considerations

### API Key Protection
- API key ditampilkan sebagai dots (••••••••••••••••)
- Tombol copy untuk mengakses API key
- Clipboard API dengan fallback untuk browser lama

### Access Control
- Hanya user yang login yang bisa akses
- Middleware auth pada route

## Future Enhancements

### 1. Playground Mode
- Real-time testing interface
- Simulation tools
- Debugging capabilities

### 2. Advanced Actions
- Remote restart
- Configuration update
- Log viewing

### 3. Real-time Updates
- WebSocket integration
- Live status updates
- Real-time metrics

### 4. Edit Mode Features
- **Partial Update**: Smart validation for basic info updates only
- **Error Handling**: Detailed validation error messages
- **Redirect Handling**: Automatic redirect back to RVM details after update

## Dependencies

### Backend
- Laravel 11
- Inertia.js
- PostgreSQL

### Frontend
- Vue.js 3
- Inertia.js
- Tailwind CSS
- Font Awesome

## File Structure

```
app/Http/Controllers/Api/RvmController.php
resources/js/Pages/Rvms/Show.vue
routes/web.php
```

## Testing

### Manual Testing
1. Navigate ke dashboard
2. Klik action menu pada RVM
3. Pilih "Lihat Details"
4. Verify semua data ditampilkan
5. Test tombol "Enter Maintenance"
6. Test tombol "Enter Playground"
7. Test copy API key functionality

### Automated Testing
- Unit tests untuk controller methods
- Integration tests untuk routes
- Component tests untuk Vue components

## Performance Considerations

### Database Queries
- Single query untuk RVM data
- Eager loading untuk relationships
- Efficient data formatting

### Frontend Performance
- Lazy loading untuk large datasets
- Efficient rendering dengan Vue 3
- Optimized CSS dengan Tailwind

## Error Handling

### Backend Errors
- RVM not found (404)
- Unauthorized access (401)
- Server errors (500)

### Frontend Errors
- Network errors
- API key copy failures
- Navigation errors

## Maintenance

### Regular Updates
- Status checking setiap 2 menit
- Data refresh setiap 30 detik
- Log rotation

### Monitoring
- Error logging
- Performance monitoring
- User activity tracking
