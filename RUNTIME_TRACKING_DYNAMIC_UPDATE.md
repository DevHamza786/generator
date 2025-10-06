# Generator Runtime Tracking - Dynamic Single Generator View

## ✅ **What's Been Implemented**

### **1. Dynamic Single Generator View**
- ✅ **Dropdown selector** - Choose which generator to view
- ✅ **Period filters** - Today, Week, Month buttons
- ✅ **Real-time data** - Calculated from actual log data
- ✅ **Single generator focus** - Shows only one generator at a time

### **2. New API Endpoint**
- ✅ **Route**: `/api/generator/runtime`
- ✅ **Parameters**: `generator_id`, `period` (today/week/month)
- ✅ **Real-time calculation** from `GeneratorLog` data
- ✅ **Runtime statistics** for different time periods

### **3. Enhanced Dashboard Interface**
- ✅ **Generator dropdown** - Select from all available generators
- ✅ **Period buttons** - Today, Week, Month filtering
- ✅ **Dynamic content** - Updates based on selection
- ✅ **Placeholder state** - Shows instruction when no generator selected

## 📊 **API Response Example**
```json
{
  "success": true,
  "data": {
    "generator": {
      "id": "ID53da9f6e",
      "sitename": "4000 Yard Catep",
      "kva_power": "200",
      "is_active": false
    },
    "runtime": {
      "current": "0m",
      "today": "0m", 
      "week": "0m",
      "month": "0m",
      "total_minutes": {
        "current": 0,
        "today": 0,
        "week": 0,
        "month": 0
      }
    },
    "last_updated": null,
    "period": "today"
  }
}
```

## 🎯 **How It Works**

### **User Interface:**
1. **Select Generator** - Choose from dropdown
2. **Choose Period** - Today, Week, or Month
3. **View Data** - See real-time runtime statistics
4. **Switch Generators** - Change selection anytime

### **Data Calculation:**
1. **Query Logs** - Get logs for selected generator and period
2. **Filter Running** - Only include logs where `GS=true`
3. **Calculate Runtime** - Time difference between first and last log
4. **Format Display** - Convert minutes to human-readable format

### **Real-time Updates:**
- **Current Runtime** - Time since last log (if generator is active)
- **Today/Week/Month** - Total runtime for each period
- **Status Indicators** - Active/Inactive based on recent data

## 🔧 **Technical Implementation**

### **API Controller** (`app/Http/Controllers/Api/GeneratorController.php`)
```php
public function getGeneratorRuntime(Request $request)
{
    // Get generator info
    // Calculate time ranges based on period
    // Query logs for the generator
    // Calculate runtime statistics
    // Return formatted data
}
```

### **JavaScript Functions**
```javascript
// Load runtime data for selected generator
function loadGeneratorRuntime(generatorId, period)

// Display the runtime data
function displayGeneratorRuntime(data)

// Show placeholder when no generator selected
function showRuntimePlaceholder()

// Show error if data loading fails
function showRuntimeError(message)
```

### **Event Handlers**
```javascript
// Generator dropdown change
$('#runtimeGeneratorFilter').on('change', function() {
    loadGeneratorRuntime(generatorId, period);
});

// Period button clicks
$('.btn-group .btn').on('click', function() {
    loadGeneratorRuntime(generatorId, period);
});
```

## 🎨 **Visual Features**

### **Generator Card Display:**
- ✅ **Generator Info** - Name, ID, KVA power rating
- ✅ **Status Badge** - Active/Inactive with color coding
- ✅ **Runtime Stats** - Current, Today, Week, Month
- ✅ **Last Updated** - Timestamp of most recent data

### **Interactive Elements:**
- ✅ **Dropdown** - Generator selection
- ✅ **Period Buttons** - Time period filtering
- ✅ **Status Indicators** - Green/red dots for active/inactive
- ✅ **Error Handling** - Retry button on failures

## 📈 **Benefits**

### **For Users:**
- ✅ **Focused View** - See one generator at a time
- ✅ **Real-time Data** - Actual runtime from logs
- ✅ **Easy Navigation** - Simple dropdown selection
- ✅ **Period Filtering** - Different time ranges

### **For System:**
- ✅ **Efficient Queries** - Only load data for selected generator
- ✅ **Scalable Design** - Works with any number of generators
- ✅ **Error Handling** - Graceful failure recovery
- ✅ **Performance** - No more loading all generators at once

## 🚀 **Usage Instructions**

### **1. Select Generator:**
- Click the dropdown in the "Generator Runtime Tracking" section
- Choose any generator from the list

### **2. Choose Time Period:**
- Click "Today", "Week", or "Month" buttons
- Data will update automatically

### **3. View Runtime Data:**
- **Current Runtime** - Time since last log (if active)
- **Today** - Total runtime today
- **This Week** - Total runtime this week  
- **This Month** - Total runtime this month

### **4. Switch Generators:**
- Change dropdown selection anytime
- Data updates immediately

## ✅ **Status: Fully Implemented and Working!**

Your Generator Runtime Tracking section now:
- ✅ **Shows one generator at a time** - No more static grid
- ✅ **Dynamic data** - Calculated from real log data
- ✅ **Dropdown selection** - Easy generator switching
- ✅ **Period filtering** - Today, Week, Month options
- ✅ **Real-time updates** - Based on actual generator activity
- ✅ **Error handling** - Graceful failure recovery

**No more static data! Everything is now dynamic and interactive!** 🚀
