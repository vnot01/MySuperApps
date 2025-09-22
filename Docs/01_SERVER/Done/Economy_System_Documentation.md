# Economy System Documentation

## Overview
The Economy System is a core component of the MyRVM-Platform that manages rewards, vouchers, and transactions for the RVM ecosystem.

## Architecture
- **Reward Calculation Engine** - Dynamic reward calculation based on waste type, quality, and weight
- **Transaction Processing** - Secure transaction handling with balance management
- **Voucher System** - Discount and promotional voucher management
- **User Balance Management** - Real-time balance tracking and updates

## Key Components

### 1. Reward Calculation
- **Base Rates** - Per-kg rates for different waste types
- **Quality Multipliers** - Grade-based reward adjustments
- **Confidence Factors** - AI confidence-based adjustments
- **Dynamic Pricing** - Market-based rate adjustments

### 2. Transaction System
- **Credit Transactions** - Reward additions
- **Debit Transactions** - Voucher redemptions
- **Balance Tracking** - Real-time balance updates
- **Transaction History** - Complete audit trail

### 3. Voucher Management
- **Voucher Types** - Percentage and fixed amount discounts
- **Usage Limits** - Per-user and total usage restrictions
- **Validity Periods** - Time-based voucher validity
- **Minimum Purchase** - Purchase amount requirements

## Database Schema

### Tables
- `user_balances` - User balance information
- `transactions` - Transaction records
- `vouchers` - Voucher definitions
- `voucher_redemptions` - Voucher usage tracking
- `reward_rules` - Dynamic reward calculation rules

### Key Relationships
- Users → Balances (1:1)
- Users → Transactions (1:many)
- Vouchers → Redemptions (1:many)
- Transactions → Sourceable (polymorphic)

## API Endpoints

### Reward Management
- `POST /api/rewards/calculate` - Calculate reward amount
- `POST /api/rewards/process` - Process reward transaction
- `GET /api/rewards/history` - Get reward history

### Voucher Management
- `GET /api/vouchers/available` - Get available vouchers
- `POST /api/vouchers/redeem` - Redeem voucher
- `GET /api/vouchers/history` - Get voucher history

### Balance Management
- `GET /api/balance` - Get user balance
- `GET /api/balance/transactions` - Get transaction history

## Business Logic

### Reward Calculation Formula
```
Reward = Base Rate × Weight × Quality Multiplier × Confidence Factor
```

### Quality Grades
- **Grade A** - Excellent condition (1.2x multiplier)
- **Grade B** - Good condition (1.0x multiplier)
- **Grade C** - Fair condition (0.8x multiplier)
- **Grade D** - Poor condition (0.5x multiplier)

### Waste Type Base Rates
- **Plastic** - 5,000 IDR per kg
- **Glass** - 3,000 IDR per kg
- **Metal** - 8,000 IDR per kg
- **Paper** - 2,000 IDR per kg
- **Other** - 1,000 IDR per kg

## Security Features
- **Transaction Validation** - Comprehensive input validation
- **Balance Verification** - Pre-transaction balance checks
- **Audit Trail** - Complete transaction logging
- **Fraud Prevention** - Rate limiting and anomaly detection

## Performance Optimization
- **Redis Caching** - Balance and rate caching
- **Database Indexing** - Optimized query performance
- **Batch Processing** - Bulk transaction handling
- **Async Processing** - Non-blocking transaction processing

## Status
🟢 **Production Ready** - Core economy system implemented and tested

## Related Documentation
- [Database Schema](Database_Schema.md)
- [API Endpoints](API_Endpoints_Documentation.md)
- [Core Implementation](Core_Implementation.md)
