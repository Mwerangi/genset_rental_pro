# MILELE POWER - COMPLETE SYSTEM DEVELOPMENT ROADMAP
## Generator Rental & Logistics Management System
### Master Blueprint Document

**Version**: 3.0  
**Last Updated**: March 18, 2026  
**Document Type**: Complete Development Roadmap  
**Purpose**: Master reference for system design, development, and deployment  
**Website**: https://www.milelepower.co.tz

---

# TABLE OF CONTENTS

1. [Executive Summary](#1-executive-summary)
2. [Business Context](#2-business-context)
3. [System Architecture Overview](#3-system-architecture-overview)
4. [Complete Feature Matrix](#4-complete-feature-matrix)
5. [Database Schema Design](#5-database-schema-design)
6. [User Roles & Permissions](#6-user-roles--permissions)
7. [Development Phases](#7-development-phases)
8. [Technical Stack](#8-technical-stack)
9. [Integration Requirements](#9-integration-requirements)
10. [UI/UX Design Guidelines](#10-uiux-design-guidelines)
11. [Testing Strategy](#11-testing-strategy)
12. [Deployment Plan](#12-deployment-plan)
13. [Maintenance & Support](#13-maintenance--support)

---

# 1. EXECUTIVE SUMMARY

## 1.1 System Overview

**Milele Power Generator Rental Management System** is a comprehensive enterprise solution for managing generator rental operations, fleet management, logistics, and financial operations specifically designed for the cold chain and logistics industry in Tanzania.

**Company**: MILELE POWER LTD  
**Location**: Plot No. 80, Mikocheni Industrial Area, Coca Cola Road - Dar Es Salaam, Tanzania  
**Mission**: "Powering your cold chain logistics — from dock to destination. Keeping It Cool, Wherever You Go."  
**Tagline**: "Reliable Power, Anytime, Anywhere!"  

**Core Brand Values**:
1. **Quality Services** - "EXCEEDING EXPECTATIONS, EVERYTIME 💯"
2. **Reliability & Availability** - "Power You Can Trust, Anytime, Anywhere 🛠️"
3. **Customer Satisfaction** - "Your Power, Our Priority 😊👍"
4. **Innovation** - "Advancing Solutions for Brighter Future 🔬"
5. **Safety & Sustainability** - "Powering Progress, Protecting Future 🌍"
6. **Commitment To Excellence** - "Setting the Standard in Power Solutions 🏆"

**Primary Target Markets**: 
- Meat & Poultry Processing Plants
- Dairy Industries
- Seafood Processing Companies
- Fruit & Vegetable Exporters
- Pharmaceutical Cold Storage
- Frozen Food Warehouses

**Core Services**:
- Clip-on Gensets (20ESX) Rental
- Underslung Gensets Rental
- Tailored Rental Packages
- Eco-Friendly & Cost-Effective Solutions
- Comprehensive Support & Maintenance

**Core Business Problem Solved**:
- Complete rental lifecycle management (quote → booking → delivery → billing → payment)
- Fleet optimization and maintenance
- Real-time equipment monitoring
- Financial management and compliance (TRA/VAT)
- Customer relationship management
- Cold chain logistics power continuity

## 1.2 System Scope

### In Scope:
✅ User management and authentication  
✅ Client relationship management (CRM)  
✅ Fleet/genset inventory management  
✅ Booking and rental management  
✅ Delivery and logistics tracking  
✅ Maintenance scheduling and tracking  
✅ Financial management (invoicing, payments, accounting)  
✅ Reporting and analytics  
✅ Document management  
✅ Tax compliance (TRA, VAT, WHT)  
✅ Mobile operations (drivers, technicians)  
✅ IoT integration (genset monitoring)  

### Out of Scope:
❌ Payroll processing (HR system)  
❌ General inventory (non-fleet items)  
❌ Manufacturing operations  
❌ E-commerce storefront  

---

# 2. BUSINESS CONTEXT

## 2.1 Industry Overview

**Milele Power Ltd. - Company Profile:**
- **Established Track Record**: 1,000+ Successful Rentals
- **Specialization**: Cold chain logistics power solutions
- **Equipment**: Clip-on Gensets (20ESX) and Underslung Gensets
- **Contact**: info@milelepower.co.tz | accounts@milelepower.co.tz
- **Operating Hours**: Monday-Friday: 9 AM - 5 PM, Saturday: 9 AM - 1 PM

**Genset Rental Industry in Tanzania:**
- Critical for cold chain logistics (pharmaceuticals, food, seafood, dairy)
- Backup power for unreliable grid supply
- Container/reefer power for transportation
- Event and construction temporary power
- Peak season demand (agricultural harvest seasons)

**Key Business Models:**
1. **Daily/Weekly/Monthly Rentals** (Short-term power needs)
2. **Long-term Contracts** (1-5 years) for major cold chain operators
3. **Power-as-a-Service** (pay per kWh) - Emerging model
4. **Full-service packages** (equipment + fuel + maintenance + monitoring)

## 2.2 Regulatory Environment (Tanzania)

### Tax Requirements:
- **VAT**: 18% standard rate
- **Withholding Tax**: Various rates (2%, 5%, 10%)
- **TRA EFD/VFD**: Mandatory electronic fiscal devices
- **VRN**: VAT Registration Number validation
- **TIN**: Tax Identification Number

### Industry Compliance:
- OSHA safety standards
- Environmental regulations (noise, emissions)
- Import/export licenses for equipment
- Insurance requirements
- Fuel storage regulations

## 2.3 Business Metrics (KPIs)

### Financial KPIs:
- Revenue per genset per month
- Revenue per client
- Average daily rate (ADR)
- Days Sales Outstanding (DSO)
- Bad debt ratio
- Profit margin per rental

### Operational KPIs:
- Fleet utilization rate
- Average rental duration
- On-time delivery rate
- Equipment downtime percentage
- Maintenance cost per genset
- Fuel consumption efficiency

### Customer KPIs:
- Customer acquisition cost (CAC)
- Customer lifetime value (CLV)
- Churn rate
- Net Promoter Score (NPS)
- Average booking value

---

# 3. SYSTEM ARCHITECTURE OVERVIEW

## 3.1 Dual-System Architecture (Public + Private)

**CRITICAL DESIGN DECISION**: The system is architected as TWO distinct but integrated applications:

### **System A: Public Marketing Website** (No Login Required)
- URL: `https://www.milelepower.co.tz`
- Purpose: Marketing, lead generation, quote requests
- Users: Anonymous visitors, prospects
- Features: Company info, services, quote request form

### **System B: Admin Management Panel** (Authentication Required)
- URL: `https://app.milelepower.co.tz` or `https://www.milelepower.co.tz/admin`
- Purpose: Operations, bookings, fleet, financials
- Users: Internal staff (admin, managers, drivers, technicians)
- Features: Full system management

### **Integration Flow**:
```
Public Website → Quote Request → Database → Admin Panel Review → 
Quotation Sent → Client Accepts → Booking Created → Operations Flow
```

---

## 3.2 High-Level Architecture Diagram

```
┌──────────────────────────────────────────────────────────────────┐
│                      PRESENTATION LAYER                          │
├──────────────────┬───────────────────┬───────────────────────────┤
│  PUBLIC WEBSITE  │  ADMIN DASHBOARD  │  MOBILE APPS             │
│  (Marketing)     │  (Operations)     │  (Field Ops)             │
│                  │                   │                           │
│  • Home Page     │  • Dashboard      │  • Driver App            │
│  • Services      │  • Fleet Mgmt     │  • Technician App        │
│  • About         │  • Bookings       │                          │
│  • Contact       │  • Clients (CRM)  │                          │
│  ► QUOTE FORM ◄  │  • Financials     │                          │
│    (Lead Gen)    │  • Reports        │                          │
└────────┬─────────┴─────────┬─────────┴───────────┬──────────────┘
         │                   │                     │
         │ (No Auth)         │ (Auth Required)     │
         │                   │                     │
┌────────▼───────────────────▼─────────────────────▼──────────────┐
│                      APPLICATION LAYER                           │
├──────────────────────────────────────────────────────────────────┤
│  PUBLIC MODULES:                                                 │
│    • Quote Request Capture Module                                │
│    • Email Notification (to admin on new request)               │
│                                                                  │
│  ADMIN MODULES:                                                  │
│    • Quote Request Management (review, convert)                  │
│    • Quotation Generation & Sending                              │
│    • User Management                                             │
│    • Client Management (CRM)                                     │
│    • Fleet Management                                            │
│    • Booking & Rental Management                                 │
│    • Delivery & Logistics                                        │
│    • Maintenance Management                                      │
│    • Financial Management (Invoicing, Payments)                  │
│    • Reporting & Analytics                                       │
│    • Workflow Engine (Approvals)                                 │
└──────────────────────────────┬───────────────────────────────────┘
                               │
┌──────────────────────────────▼───────────────────────────────────┐
│                         SERVICE LAYER                             │
├──────────────────────────────────────────────────────────────────┤
│  • Quote Request Service (create, notify, convert)               │
│  • Authentication Service                                         │
│  • Authorization Service (RBAC)                                   │
│  • Quotation Service (generate, send, track)                     │
│  • Booking Service                                                │
│  • Invoice Service                                                │
│  • Payment Service                                                │
│  • Fleet Service                                                  │
│  • Notification Service (Email, SMS)                              │
│  • PDF Generation Service (quotes, invoices)                     │
│  • Integration Service                                            │
│  • Reporting Service                                              │
└──────────────────────────────┬───────────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│                     DATA LAYER                               │
├─────────────┬────────────────┬───────────────┬──────────────┤
│  MySQL/     │  Redis         │  File Storage │  Logs        │
│  PostgreSQL │  (Cache/Queue) │  (S3/Local)   │  (ELK Stack) │
└─────────────┴────────────────┴───────────────┴──────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│              EXTERNAL INTEGRATIONS                           │
├──────────────────────────────────────────────────────────────┤
│  • TRA EFD/VFD API                                           │
│  • Payment Gateways (M-Pesa, Tigo Pesa, Airtel Money)       │
│  • SMS Gateway (Twilio, Africa's Talking)                    │
│  • Email Service (SendGrid, AWS SES)                         │
│  • GPS/Telematics Platform                                   │
│  • IoT Platform (Genset monitoring)                          │
│  • Accounting Software (QuickBooks, Xero)                    │
│  • Google Maps API                                           │
└──────────────────────────────────────────────────────────────┘
```

## 3.3 Complete Sales Funnel Flow (NEW ARCHITECTURE)

### 📊 From Prospect to Payment: The Complete Journey

```
┌────────────────────────────────────────────────────────────────┐
│                    STAGE 1: LEAD CAPTURE                       │
│                    (Public Website - No Login)                 │
├────────────────────────────────────────────────────────────────┤
│                                                                │
│  1. Prospect visits www.milelepower.co.tz                     │
│  2. Clicks "Get Quote" button (red CTA)                       │
│  3. Fills out quote request form:                             │
│     • Full name, email, phone                                 │
│     • Company name (optional)                                 │
│     • Genset model needed                                     │
│     • Start date & rental days                                │
│     • Delivery & pickup locations                             │
│     • Additional requirements                                 │
│  4. Submits form                                              │
│                                                                │
│  ✓ RESULT: Quote request saved to database                    │
│  ✓ Email sent to admin: "New Quote Request: QR-2026-0001"    │
│  ✓ Auto-reply sent to prospect: "We'll contact you soon"     │
│                                                                │
│  DATABASE: `quote_requests` table                             │
│  STATUS: new                                                   │
│                                                                │
└─────────────────────────┬──────────────────────────────────────┘
                          │
                          ▼
┌────────────────────────────────────────────────────────────────┐
│                    STAGE 2: ADMIN REVIEW                       │
│                (Admin Panel - Login Required)                  │
├────────────────────────────────────────────────────────────────┤
│                                                                │
│  5. Admin logs into app.milelepower.co.tz/admin              │
│  6. Sees notification: "3 New Quote Requests"                │
│  7. Navigates to Quote Requests dashboard                     │
│  8. Reviews request QR-2026-0001:                             │
│     • Validates prospect information                          │
│     • Checks genset availability                              │
│     • Verifies locations are serviceable                      │
│     • Calculates rough pricing                                │
│  9. Marks as "reviewed"                                       │
│                                                                │
│  DATABASE: `quote_requests` table                             │
│  STATUS: new → reviewed                                        │
│                                                                │
└─────────────────────────┬──────────────────────────────────────┘
                          │
                          ▼
┌────────────────────────────────────────────────────────────────┐
│                STAGE 3: QUOTATION GENERATION                   │
│                    (Admin Panel)                               │
├────────────────────────────────────────────────────────────────┤
│                                                                │
│ 10. Admin clicks "Generate Quotation" button                  │
│ 11. System pre-fills quotation with prospect data             │
│ 12. Admin adds/edits:                                         │
│     • Genset details & daily rate                             │
│     • Number of days                                          │
│     • Delivery charges                                        │
│     • Crane/lift charges (if needed)                          │
│     • Fuel charges                                            │
│     • Discounts                                               │
│     • VAT (18%)                                               │
│ 13. Reviews total amount                                      │
│ 14. Sets quotation validity (e.g., 7 days)                   │
│ 15. Generates PDF (branded template)                          │
│ 16. Sends via email to prospect                               │
│                                                                │
│  DATABASE: `quotations` table created                          │
│  LINK: quote_request_id → quotations.id                       │
│  STATUS: quote_requests.status → quoted                       │
│  FILE: quotation_2026_0001.pdf generated                      │
│                                                                │
│  EMAIL: Quotation sent to prospect with PDF attached          │
│                                                                │
└─────────────────────────┬──────────────────────────────────────┘
                          │
                          ▼
┌────────────────────────────────────────────────────────────────┐
│                STAGE 4: PROSPECT DECISION                      │
│                 (Email Communication)                          │
├────────────────────────────────────────────────────────────────┤
│                                                                │
│ 17. Prospect receives quotation PDF                           │
│ 18. Reviews pricing and terms                                 │
│ 19. Responds to Milele Power:                                 │
│                                                                │
│     OPTION A: ACCEPTS ✅                                       │
│     • Prospect replies: "Yes, I accept the quote"             │
│     • May request minor modifications                         │
│                                                                │
│     OPTION B: REJECTS ❌                                       │
│     • Prospect declines (price too high, found alternative)   │
│     • Admin marks quote_request as "rejected"                 │
│     • Remains in system for future follow-up                  │
│                                                                │
│     OPTION C: NO RESPONSE ⏸️                                   │
│     • Quotation expires after validity period                 │
│     • Admin can send reminder                                 │
│     • Remains as prospect for future marketing               │
│                                                                │
└─────────────────────────┬──────────────────────────────────────┘
                          │
                    (IF ACCEPTED)
                          ▼
┌────────────────────────────────────────────────────────────────┐
│           STAGE 5: CONVERSION TO BOOKING                       │
│                    (Admin Panel)                               │
├────────────────────────────────────────────────────────────────┤
│                                                                │
│ 20. Admin receives acceptance from prospect                   │
│ 21. Admin navigates to accepted quotation                     │
│ 22. Clicks "Convert to Booking" button                        │
│ 23. System performs conversion:                               │
│                                                                │
│     STEP A: Create Client Record                              │
│     • Check if client exists (by email/phone)                 │
│     • If new: Create client record in `clients` table         │
│     • Copy prospect data to client profile                    │
│     • Assign client number (CL-2026-0001)                     │
│                                                                │
│     STEP B: Create Booking                                    │
│     • Create booking in `bookings` table                      │
│     • Copy quotation items to booking                         │
│     • Assign booking number (BK-2026-0001)                    │
│     • Set status: "Pending" or "Confirmed"                    │
│     • Link: client_id, quotation_id                           │
│                                                                │
│     STEP C: Update Links                                      │
│     • quote_requests.status → "converted"                     │
│     • quote_requests.booking_id → new booking ID              │
│     • quotations.status → "accepted"                          │
│     • Record conversion date                                  │
│                                                                │
│  RESULT:                                                       │
│  ✓ Prospect is now a Client (in CRM)                          │
│  ✓ Quote Request is now a Booking                             │
│  ✓ Ready for operations workflow                              │
│                                                                │
└─────────────────────────┬──────────────────────────────────────┘
                          │
                          ▼
┌────────────────────────────────────────────────────────────────┐
│              STAGE 6: OPERATIONS WORKFLOW                      │
│               (Existing System Features)                       │
├────────────────────────────────────────────────────────────────┤
│                                                                │
│ 24. Booking appears in bookings dashboard                     │
│ 25. Operations Manager assigns:                               │
│     • Specific genset                                         │
│     • Delivery date & time                                    │
│     • Driver                                                  │
│     • Vehicle                                                 │
│ 26. Delivery Order created                                    │
│ 27. Driver receives notification                              │
│ 28. Genset delivered to client site                           │
│ 29. Proof of Delivery (POD) captured                          │
│ 30. Booking status → "Active/Rented"                          │
│                                                                │
└─────────────────────────┬──────────────────────────────────────┘
                          │
                          ▼
┌────────────────────────────────────────────────────────────────┐
│              STAGE 7: INVOICING & PAYMENT                      │
│              (Finance & Accounting Module)                     │
├────────────────────────────────────────────────────────────────┤
│                                                                │
│ 31. System generates invoice (auto or manual)                 │
│ 32. Invoice includes:                                         │
│     • Rental charges (from quotation)                         │
│     • Actual delivery charges                                 │
│     • Fuel charges (actual)                                   │
│     • VAT (18%)                                               │
│     • Withholding Tax (if applicable)                         │
│     • Payment terms (e.g., Net 30)                            │
│ 33. Invoice sent to client                                    │
│ 34. Client makes payment (M-Pesa, bank, cash)                │
│ 35. Finance records payment                                   │
│ 36. Invoice status → "Paid"                                   │
│ 37. Receipt generated & sent                                  │
│                                                                │
└─────────────────────────┬──────────────────────────────────────┘
                          │
                          ▼
┌────────────────────────────────────────────────────────────────┐
│                    STAGE 8: COMPLETION                         │
│                (End of Rental Cycle)                           │
├────────────────────────────────────────────────────────────────┤
│                                                                │
│ 38. Genset returned (pickup scheduled)                        │
│ 39. Return inspection done                                    │
│ 40. Booking status → "Completed"                              │
│ 41. Client marked as "Active" in CRM                          │
│ 42. Ready for future bookings (no quote needed)               │
│                                                                │
│  FUTURE BOOKINGS:                                             │
│  • Existing client can book directly in system                │
│  • OR admin creates booking for them                          │
│  • OR client uses self-service portal (Phase 3)               │
│                                                                │
└────────────────────────────────────────────────────────────────┘
```

### 🎯 Key Benefits of This Flow

1. **Low Barrier to Entry**: No login required for prospects
2. **Professional First Impression**: Modern website builds trust
3. **Lead Capture**: Every inquiry saved, no leads lost
4. **Clear Sales Process**: Defined stages from prospect to customer
5. **Data Continuity**: Quote request data flows through entire lifecycle
6. **Conversion Tracking**: Know where customers came from
7. **Marketing Insights**: Analyze which genset models are most requested
8. **Follow-up Capability**: Re-engage prospects who didn't convert

### 📈 Metrics to Track

- **Lead Generation**: Quote requests per day/week/month
- **Response Time**: Time from request to quotation sent
- **Conversion Rate**: % of quote requests that become bookings
- **Revenue Pipeline**: Total value of pending quotations
- **Win/Loss Reasons**: Why prospects accept or reject
- **Source Tracking**: Which marketing channels drive leads
- **Popular Models**: Most requested genset types

---

## 3.4 Technology Stack

### Backend:
- **Framework**: Laravel 12 (PHP 8.2+)
- **Database**: MySQL 8.0+ / PostgreSQL 14+
- **Cache**: Redis 7.0+
- **Queue**: Laravel Queue (Redis driver)
- **Search**: Laravel Scout + Meilisearch/Elasticsearch
- **API**: RESTful + GraphQL (optional)

### Frontend:
- **Admin Panel**: Blade Templates + Alpine.js + Tailwind CSS
- **Client Portal**: Vue.js 3 or React 18
- **Mobile**: Flutter / React Native
- **Build Tool**: Vite 5

### DevOps:
- **Server**: Ubuntu 22.04 LTS
- **Web Server**: Nginx + PHP-FPM
- **Containerization**: Docker + Docker Compose
- **CI/CD**: GitHub Actions / GitLab CI
- **Monitoring**: Laravel Telescope, Sentry
- **Logs**: ELK Stack (Elasticsearch, Logstash, Kibana)

### Third-Party Services:
- **File Storage**: AWS S3 / DigitalOcean Spaces
- **Email**: SendGrid / AWS SES
- **SMS**: Africa's Talking / Twilio
- **Maps**: Google Maps API
- **Analytics**: Google Analytics, Mixpanel

---

# 4. COMPLETE FEATURE MATRIX

## 4.1 MODULE BREAKDOWN

### 🌐 **MODULE 0: PUBLIC WEBSITE & QUOTE REQUEST SYSTEM** (NEW - PRIORITY 1)

#### 0.1 Public Landing Page
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Modern responsive website | ❌ Missing | **CRITICAL** | Medium |
| Hero section (black/red theme) | ❌ Missing | **CRITICAL** | Low |
| Services showcase (6 cards) | ❌ Missing | High | Low |
| Target clients section | ❌ Missing | High | Low |
| Company stats (1000+ rentals) | ❌ Missing | Medium | Low |
| Contact information section | ❌ Missing | High | Low |
| Footer with company details | ❌ Missing | High | Low |
| SEO optimization | ❌ Missing | High | Medium |
| Mobile responsive design | ❌ Missing | **CRITICAL** | Medium |
| Fast page load (<2s) | ❌ Missing | High | Medium |

#### 0.2 Quote Request Form (Lead Generation)
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| "Get Quote" button (prominent) | ❌ Missing | **CRITICAL** | Low |
| Quote request form modal/page | ❌ Missing | **CRITICAL** | Medium |
| Full name field | ❌ Missing | **CRITICAL** | Low |
| Email address field (validated) | ❌ Missing | **CRITICAL** | Low |
| Phone number field (with validation) | ❌ Missing | **CRITICAL** | Low |
| Company name (optional) | ❌ Missing | High | Low |
| Genset model selector (from DB) | ❌ Missing | **CRITICAL** | Medium |
| Requested start date | ❌ Missing | **CRITICAL** | Low |
| Number of rental days | ❌ Missing | **CRITICAL** | Low |
| Delivery location (address) | ❌ Missing | **CRITICAL** | Medium |
| Pickup location (address) | ❌ Missing | **CRITICAL** | Medium |
| Google Maps integration (location) | ❌ Missing | High | High |
| Additional requirements (textarea) | ❌ Missing | Medium | Low |
| Terms & conditions checkbox | ❌ Missing | High | Low |
| CAPTCHA/spam protection | ❌ Missing | High | Low |
| Form validation (client-side) | ❌ Missing | **CRITICAL** | Medium |
| Form validation (server-side) | ❌ Missing | **CRITICAL** | Medium |
| Success message after submission | ❌ Missing | High | Low |
| Auto-reply email to prospect | ❌ Missing | High | Medium |

#### 0.3 Quote Request Backend
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Save request to database | ❌ Missing | **CRITICAL** | Low |
| Auto-generate request number (QR-2026-0001) | ❌ Missing | High | Low |
| Email notification to admin | ❌ Missing | **CRITICAL** | Medium |
| SMS notification to admin (optional) | ❌ Missing | Medium | Medium |
| Track source (website/phone/email) | ❌ Missing | Medium | Low |
| IP address logging | ❌ Missing | Low | Low |
| Duplicate detection | ❌ Missing | Medium | Medium |
| Rate limiting (spam prevention) | ❌ Missing | High | Medium |

#### 0.4 Admin Quote Request Management
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| View all quote requests | ❌ Missing | **CRITICAL** | Medium |
| Filter by status (new/reviewed/quoted/converted) | ❌ Missing | High | Medium |
| Mark as reviewed | ❌ Missing | High | Low |
| Generate quotation from request | ❌ Missing | **CRITICAL** | High |
| Send quotation to prospect | ❌ Missing | **CRITICAL** | Medium |
| Convert to client + booking | ❌ Missing | **CRITICAL** | High |
| Reject request (with reason) | ❌ Missing | High | Medium |
| View request history/timeline | ❌ Missing | Medium | Medium |
| Export quote requests | ❌ Missing | Low | Low |
| Search quote requests | ❌ Missing | High | Medium |
| Quote request dashboard/stats | ❌ Missing | Medium | Medium |

#### 0.5 Prospect to Customer Conversion
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Create client from prospect | ❌ Missing | **CRITICAL** | Medium |
| Convert quote request to booking | ❌ Missing | **CRITICAL** | High |
| Link quotation to booking | ❌ Missing | High | Medium |
| Maintain conversion tracking | ❌ Missing | Medium | Medium |
| Conversion rate analytics | ❌ Missing | Low | High |

---

### 🔐 **MODULE 1: AUTHENTICATION & USER MANAGEMENT**

#### 1.1 User Authentication
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Email/password login | ✅ Exists | Critical | Low |
| Two-factor authentication (2FA) | ❌ Missing | High | Medium |
| Social login (Google/Facebook) | ❌ Missing | Low | Low |
| Single Sign-On (SSO) | ❌ Missing | Low | High |
| Password reset flow | ✅ Exists | Critical | Low |
| Session management | ✅ Exists | Critical | Low |
| Remember me functionality | ✅ Exists | Medium | Low |
| Login attempt limiting | ❌ Missing | High | Low |
| IP whitelisting | ❌ Missing | Medium | Medium |

#### 1.2 User Management
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Create/Read/Update/Delete users | ✅ Exists | Critical | Low |
| User profiles (basic) | ✅ Exists | Critical | Low |
| User profiles (extended) | ⚠️ Partial | High | Medium |
| Role-based access control (RBAC) | ✅ Exists | Critical | Medium |
| Permission management | ✅ Exists | Critical | Medium |
| User activity logs | ❌ Missing | High | Medium |
| User status (active/inactive/suspended) | ⚠️ Partial | High | Low |
| Bulk user operations | ❌ Missing | Low | Low |

#### 1.3 Staff Management
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Employee records | ⚠️ Partial | High | Medium |
| Department/position hierarchy | ❌ Missing | Medium | Medium |
| Staff skills and certifications | ❌ Missing | Medium | Medium |
| Shift scheduling | ❌ Missing | High | High |
| Availability calendar | ❌ Missing | Medium | Medium |
| Commission/incentive tracking | ❌ Missing | Medium | High |
| Performance metrics | ❌ Missing | Medium | High |
| Driver license tracking | ❌ Missing | High | Low |
| Emergency contacts | ❌ Missing | Medium | Low |

#### 1.4 Roles & Permissions
**Pre-defined Roles:**
- **Super Admin**: Full system access
- **Admin**: All except critical system config
- **Finance Manager**: Financial operations
- **Operations Manager**: Fleet and bookings
- **Sales Manager**: CRM and quotations
- **Dispatcher**: Delivery management
- **Driver**: Mobile app, delivery ops
- **Technician**: Maintenance operations
- **Client Admin**: Company account management
- **Client User**: Booking and viewing
- **Accountant**: Financial reporting
- **Customer Support**: Client inquiries

---

### 👥 **MODULE 2: CLIENT RELATIONSHIP MANAGEMENT (CRM)**

#### 2.1 Client Management
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Client company profiles | ✅ Exists | Critical | Low |
| Multiple contacts per client | ✅ Exists | Critical | Medium |
| Contact authorization levels | ✅ Exists | High | Medium |
| Client categorization | ✅ Exists | High | Low |
| Client tagging system | ✅ Exists | High | Low |
| Client addresses (multiple) | ✅ Exists | Critical | Medium |
| Client documents management | ✅ Exists | High | Medium |
| Document approval workflow | ✅ Exists | Medium | Medium |
| Credit limit management | ✅ Exists | Critical | Medium |
| Payment terms configuration | ⚠️ Partial | High | Low |
| Client status tracking | ✅ Exists | High | Low |
| Profile completion tracking | ✅ Exists | Medium | Low |

#### 2.2 Contract Management
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Master Service Agreements (MSA) | ❌ Missing | High | High |
| Contract document upload | ❌ Missing | High | Low |
| Contract version control | ❌ Missing | Medium | Medium |
| Contract renewal alerts | ❌ Missing | High | Medium |
| SLA definitions | ❌ Missing | High | High |
| Rate cards per client | ❌ Missing | High | Medium |
| Volume discount tiers | ❌ Missing | Medium | Medium |
| Contract templates | ❌ Missing | Medium | Medium |
| E-signature integration | ❌ Missing | Low | High |

#### 2.3 Credit & Risk Management
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Credit scoring/rating | ❌ Missing | High | High |
| Payment behavior tracking | ⚠️ Partial | High | Medium |
| Average days to pay | ❌ Missing | High | Medium |
| Credit limit utilization | ⚠️ Partial | High | Medium |
| Overdue payment alerts | ⚠️ Partial | Critical | Low |
| Blacklist/watchlist | ❌ Missing | High | Low |
| Bank guarantee tracking | ❌ Missing | Medium | Low |
| Security deposit management | ❌ Missing | High | Medium |
| Insurance certificate tracking | ⚠️ Partial | High | Low |

#### 2.4 Sales Pipeline (CRM)
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Lead management | ❌ Missing | High | Medium |
| Opportunity tracking | ❌ Missing | High | Medium |
| Quotation system | ❌ Missing | Critical | High |
| Quote versions | ❌ Missing | Medium | Medium |
| Quote approval workflow | ❌ Missing | Medium | Medium |
| Quote to booking conversion | ❌ Missing | High | Medium |
| Sales pipeline stages | ❌ Missing | High | Medium |
| Conversion tracking | ❌ Missing | Medium | Medium |
| Lost opportunity reasons | ❌ Missing | Low | Low |

#### 2.5 Communication Management
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Email integration | ❌ Missing | Medium | High |
| Call log tracking | ❌ Missing | Medium | Medium |
| Meeting notes | ❌ Missing | Low | Low |
| Complaint management | ❌ Missing | High | Medium |
| Communication history | ❌ Missing | Medium | Medium |
| Template management | ❌ Missing | Medium | Low |
| Automated follow-ups | ❌ Missing | Medium | High |

---

### ⚡ **MODULE 3: FLEET/GENSET MANAGEMENT**

#### 3.1 Asset Inventory
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Genset basic information | ✅ Exists | Critical | Low |
| Equipment specifications | ✅ Exists | Critical | Low |
| Physical dimensions | ✅ Exists | Medium | Low |
| Manufacturer/model details | ✅ Exists | High | Low |
| Serial number tracking | ✅ Exists | Critical | Low |
| Purchase information | ✅ Exists | High | Low |
| Rental rates (daily/weekly/monthly) | ✅ Exists | Critical | Low |
| Equipment photos | ⚠️ Partial | High | Low |
| QR code generation | ❌ Missing | Medium | Low |
| Barcode scanning | ❌ Missing | Low | Medium |

#### 3.2 Status & Availability
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Real-time status tracking | ✅ Exists | Critical | Medium |
| Availability calendar | ⚠️ Partial | Critical | High |
| Booking conflict detection | ✅ Exists | Critical | Medium |
| Maintenance scheduling impact | ⚠️ Partial | High | High |
| Multi-genset availability search | ❌ Missing | High | Medium |
| Reservation holds | ❌ Missing | Medium | Medium |

#### 3.3 GPS & Location Tracking
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Real-time GPS tracking | ✅ Exists | High | High |
| Location history | ✅ Exists | High | Medium |
| Geofencing | ⚠️ Partial | Medium | High |
| Movement alerts | ⚠️ Partial | High | Medium |
| Route playback | ❌ Missing | Low | Medium |
| Speed monitoring | ⚠️ Partial | Medium | Low |
| Distance calculations | ⚠️ Partial | Medium | Low |

#### 3.4 Fuel Management
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Fuel level monitoring | ❌ Missing | Critical | High |
| Fuel consumption tracking | ⚠️ Partial | Critical | High |
| Fuel delivered vs returned | ⚠️ Partial | Critical | Medium |
| Fuel theft alerts | ❌ Missing | High | High |
| Fuel cost per booking | ❌ Missing | High | Medium |
| Fuel purchase tracking | ❌ Missing | High | Medium |
| Fuel sensor integration | ❌ Missing | High | High |
| Fuel efficiency reporting | ❌ Missing | Medium | Medium |
| Refueling logs | ❌ Missing | High | Low |

#### 3.5 IoT & Telematics
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Real-time genset status | ❌ Missing | High | High |
| Running hours auto-update | ⚠️ Partial | High | High |
| Power output monitoring | ❌ Missing | Medium | High |
| Voltage/frequency monitoring | ❌ Missing | Medium | High |
| Temperature sensors | ❌ Missing | Medium | High |
| Load monitoring | ❌ Missing | Medium | High |
| Fault code detection | ❌ Missing | High | High |
| Remote start/stop | ❌ Missing | Low | High |
| Alert notifications | ⚠️ Partial | High | Medium |
| Data analytics dashboard | ❌ Missing | Medium | High |

#### 3.6 Maintenance Management
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Preventive maintenance scheduling | ✅ Exists | Critical | High |
| Maintenance history | ✅ Exists | High | Medium |
| Work order generation | ✅ Exists | High | Medium |
| Technician assignment | ⚠️ Partial | High | Medium |
| Maintenance checklists | ❌ Missing | High | Medium |
| Parts inventory tracking | ❌ Missing | High | High |
| Maintenance cost tracking | ⚠️ Partial | High | Medium |
| Service interval alerts | ⚠️ Partial | High | Medium |
| Warranty tracking | ❌ Missing | Medium | Medium |
| Downtime analysis | ❌ Missing | High | Medium |
| MTBF/MTTR metrics | ❌ Missing | Medium | High |

#### 3.7 Asset Lifecycle
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Depreciation tracking | ❌ Missing | High | Medium |
| Book value calculation | ❌ Missing | High | Medium |
| Residual value estimation | ❌ Missing | Medium | Medium |
| Disposal planning | ❌ Missing | Low | Low |
| Sale/scrapping records | ❌ Missing | Low | Low |
| Asset replacement planning | ❌ Missing | Medium | High |
| Total cost of ownership | ❌ Missing | Medium | High |

---

### 📅 **MODULE 4: BOOKING & RENTAL MANAGEMENT**

#### 4.1 Booking Creation
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Create booking (admin) | ✅ Exists | Critical | Medium |
| Create booking (client portal) | ⚠️ Partial | High | Medium |
| Quick booking | ❌ Missing | Medium | Low |
| Recurring bookings | ❌ Missing | Medium | High |
| Multi-genset booking | ⚠️ Partial | High | Medium |
| Availability check | ✅ Exists | Critical | Medium |
| Rate calculation | ✅ Exists | Critical | Medium |
| Optional charges (transport, crane) | ✅ Exists | High | Low |
| Custom discounts | ❌ Missing | High | Medium |
| Booking templates | ❌ Missing | Low | Medium |

#### 4.2 Booking Workflow
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Draft bookings | ✅ Exists | High | Low |
| Pending approval | ✅ Exists | Critical | Medium |
| Multi-level approval workflow | ✅ Exists | High | High |
| Approval notifications | ⚠️ Partial | High | Medium |
| Booking modifications | ⚠️ Partial | High | Medium |
| Modification approval | ❌ Missing | Medium | Medium |
| Booking cancellation | ✅ Exists | High | Low |
| Cancellation fees | ❌ Missing | Medium | Medium |
| Booking extensions | ⚠️ Partial | High | Medium |
| Extension approval | ❌ Missing | Medium | Low |

#### 4.3 Booking Lifecycle
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Status tracking (draft→completed) | ✅ Exists | Critical | Medium |
| Automatic status updates | ⚠️ Partial | High | Medium |
| Booking history/logs | ✅ Exists | High | Medium |
| Delivery scheduling | ⚠️ Partial | Critical | High |
| Return scheduling | ⚠️ Partial | Critical | Medium |
| Overdue tracking | ✅ Exists | High | Medium |
| Late return penalties | ⚠️ Partial | High | Medium |
| Early return credits | ❌ Missing | Low | Medium |

#### 4.4 Special Requirements
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Delivery instructions | ⚠️ Partial | High | Low |
| Site access information | ⚠️ Partial | High | Low |
| Power requirements | ⚠️ Partial | Medium | Low |
| Installation notes | ❌ Missing | Medium | Low |
| Safety requirements | ❌ Missing | Medium | Low |
| Client contact for delivery | ✅ Exists | High | Low |
| Emergency contact | ✅ Exists | High | Low |

---

### 🚚 **MODULE 5: DELIVERY & LOGISTICS**

#### 5.1 Delivery Management
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Create delivery order | ✅ Exists | Critical | Medium |
| Driver assignment | ✅ Exists | High | Medium |
| Vehicle assignment | ⚠️ Partial | High | Medium |
| Multi-stop deliveries | ❌ Missing | Medium | High |
| Delivery scheduling | ✅ Exists | High | Medium |
| Route planning | ⚠️ Partial | Medium | High |
| Route optimization | ❌ Missing | Medium | High |
| ETA calculations | ❌ Missing | Medium | High |
| Real-time tracking | ✅ Exists | High | High |

#### 5.2 Proof of Delivery (POD)
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Digital signature capture | ⚠️ Partial | Critical | Medium |
| Photo documentation | ✅ Exists | Critical | Low |
| Condition checklist (digital) | ❌ Missing | Critical | Medium |
| GPS-stamped confirmation | ⚠️ Partial | High | Medium |
| Delivery notes | ✅ Exists | High | Low |
| Recipient details | ⚠️ Partial | High | Low |
| Timestamp recording | ✅ Exists | High | Low |
| Multiple photo angles | ⚠️ Partial | High | Low |
| POD PDF generation | ❌ Missing | Medium | Medium |

#### 5.3 Return/Pickup Management
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Schedule pickup | ⚠️ Partial | Critical | Medium |
| Pickup confirmation | ⚠️ Partial | High | Medium |
| Return condition inspection | ⚠️ Partial | Critical | Medium |
| Fuel level verification | ⚠️ Partial | Critical | Low |
| Damage documentation | ⚠️ Partial | High | Medium |
| Return receipt generation | ❌ Missing | High | Medium |
| Return discrepancy handling | ❌ Missing | High | Medium |

#### 5.4 Transport Management
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Vehicle fleet tracking | ❌ Missing | Medium | Medium |
| Driver management | ⚠️ Partial | High | Medium |
| Crane/lifting equipment | ⚠️ Partial | High | Low |
| Transport cost tracking | ❌ Missing | High | Medium |
| Fuel consumption (vehicles) | ❌ Missing | Medium | Medium |
| Vehicle maintenance | ❌ Missing | Medium | Medium |
| Driver performance metrics | ❌ Missing | Low | High |

---

### 💰 **MODULE 6: FINANCIAL MANAGEMENT**

#### 6.1 Invoicing
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Auto invoice generation | ✅ Exists | Critical | Medium |
| Manual invoice creation | ✅ Exists | High | Low |
| Invoice templates | ⚠️ Partial | High | Medium |
| Multi-currency invoices | ⚠️ Partial | Medium | Medium |
| Invoice line items | ✅ Exists | Critical | Low |
| Tax calculations (VAT, WHT) | ⚠️ Partial | Critical | Medium |
| Discounts on invoice | ❌ Missing | High | Low |
| Invoice approval workflow | ❌ Missing | Medium | Medium |
| Recurring invoices | ❌ Missing | Low | High |
| Proforma invoices | ❌ Missing | Medium | Low |
| Invoice versioning | ❌ Missing | Low | Medium |
| Credit notes | ❌ Missing | High | Medium |
| Debit notes | ❌ Missing | Low | Low |

#### 6.2 Payment Processing
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Record payments | ✅ Exists | Critical | Low |
| Multiple payment methods | ✅ Exists | High | Low |
| Partial payments | ✅ Exists | High | Medium |
| Payment allocation | ⚠️ Partial | High | Medium |
| Payment receipts | ⚠️ Partial | High | Low |
| Payment verification | ✅ Exists | High | Medium |
| Payment gateway integration | ❌ Missing | High | High |
| Mobile money (M-Pesa, Tigo, Airtel) | ❌ Missing | Critical | High |
| Bank reconciliation | ❌ Missing | High | High |
| Payment reminders | ⚠️ Partial | High | Medium |
| Overdue notifications | ⚠️ Partial | High | Medium |

#### 6.3 Refund Management
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Refund requests | ✅ Exists | High | Medium |
| Refund approval workflow | ✅ Exists | High | Medium |
| Refund processing | ✅ Exists | High | Medium |
| Refund tracking | ✅ Exists | High | Low |
| Refund reasons | ⚠️ Partial | Medium | Low |
| Partial refunds | ⚠️ Partial | Medium | Medium |

#### 6.4 Quotation System
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Create quotations | ❌ Missing | Critical | High |
| Quote templates | ❌ Missing | High | Medium |
| Quote versioning | ❌ Missing | Medium | Medium |
| Quote approval | ❌ Missing | Medium | Medium |
| Quote validity period | ❌ Missing | High | Low |
| Quote acceptance tracking | ❌ Missing | High | Medium |
| Quote to booking conversion | ❌ Missing | Critical | Medium |
| Quote comparison | ❌ Missing | Low | Medium |
| Quote PDF generation | ❌ Missing | High | Low |

#### 6.5 Accounting Integration
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Chart of accounts | ❌ Missing | Critical | High |
| General ledger | ❌ Missing | Critical | High |
| Journal entries | ❌ Missing | High | High |
| Trial balance | ❌ Missing | High | Medium |
| Profit & Loss statement | ❌ Missing | Critical | High |
| Balance sheet | ❌ Missing | Critical | High |
| Cash flow statement | ❌ Missing | High | High |
| Revenue recognition | ❌ Missing | High | High |
| Expense tracking | ⚠️ Partial | High | Medium |
| Account reconciliation | ❌ Missing | High | High |

#### 6.6 Tax Compliance (Tanzania)
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| VAT calculation (18%) | ⚠️ Partial | Critical | Low |
| VAT invoices | ⚠️ Partial | Critical | Medium |
| VRN validation | ❌ Missing | Critical | Medium |
| TIN validation | ⚠️ Partial | High | Low |
| Withholding tax | ⚠️ Partial | High | Medium |
| WHT certificates | ❌ Missing | High | Medium |
| TRA EFD/VFD integration | ❌ Missing | Critical | High |
| Electronic receipts | ❌ Missing | Critical | High |
| Z-reports | ❌ Missing | Critical | Medium |
| Tax returns | ❌ Missing | High | High |

#### 6.7 Credit Control
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Aging reports (AR) | ⚠️ Partial | Critical | Medium |
| Payment reminders | ⚠️ Partial | High | Medium |
| Dunning letters | ❌ Missing | Medium | Medium |
| Collection workflow | ❌ Missing | High | High |
| Statement of accounts | ⚠️ Partial | High | Medium |
| Account holds | ❌ Missing | High | Low |
| Payment plans | ❌ Missing | Medium | High |

---

### 📊 **MODULE 7: REPORTING & ANALYTICS**

#### 7.1 Financial Reports
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Revenue reports | ⚠️ Partial | Critical | Medium |
| Revenue by client | ⚠️ Partial | High | Medium |
| Revenue by genset | ⚠️ Partial | High | Medium |
| Revenue by period | ⚠️ Partial | High | Low |
| Profitability reports | ❌ Missing | High | High |
| Cost analysis | ❌ Missing | High | High |
| Budget vs actual | ❌ Missing | Medium | High |
| Cash flow forecast | ❌ Missing | Medium | High |

#### 7.2 Operational Reports
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Fleet utilization | ⚠️ Partial | Critical | High |
| Booking statistics | ⚠️ Partial | High | Medium |
| Delivery performance | ❌ Missing | High | Medium |
| Maintenance reports | ⚠️ Partial | High | Medium |
| Downtime analysis | ❌ Missing | High | Medium |
| Fuel consumption | ❌ Missing | High | Medium |
| Driver performance | ❌ Missing | Medium | Medium |

#### 7.3 Client Reports
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Client activity | ⚠️ Partial | High | Medium |
| Client profitability | ❌ Missing | High | High |
| Client retention | ❌ Missing | Medium | High |
| RFM analysis | ❌ Missing | Medium | High |
| Lifetime value | ❌ Missing | Medium | High |
| Payment behavior | ⚠️ Partial | High | Medium |

#### 7.4 Dashboard & KPIs
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Executive dashboard | ⚠️ Partial | High | High |
| Real-time KPI widgets | ⚠️ Partial | High | Medium |
| Custom dashboards | ❌ Missing | Medium | High |
| Role-based dashboards | ❌ Missing | Medium | Medium |
| Interactive charts | ⚠️ Partial | Medium | Medium |
| Export to Excel/PDF | ⚠️ Partial | High | Low |
| Scheduled reports | ❌ Missing | Medium | Medium |
| Alert thresholds | ❌ Missing | Medium | Medium |

---

### 🔔 **MODULE 8: NOTIFICATIONS & COMMUNICATIONS**

#### 8.1 Email Notifications
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Booking confirmations | ⚠️ Partial | Critical | Low |
| Invoice emails | ⚠️ Partial | Critical | Low |
| Payment confirmations | ⚠️ Partial | High | Low |
| Delivery notifications | ⚠️ Partial | High | Low |
| Maintenance reminders | ⚠️ Partial | High | Low |
| Overdue reminders | ⚠️ Partial | High | Low |
| Email templates | ⚠️ Partial | High | Medium |
| Template customization | ❌ Missing | Medium | Medium |

#### 8.2 SMS Notifications
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| SMS delivery updates | ❌ Missing | High | Medium |
| SMS payment reminders | ❌ Missing | High | Medium |
| SMS alerts (critical) | ❌ Missing | High | Medium |
| SMS OTP for security | ❌ Missing | Medium | Medium |
| Bulk SMS | ❌ Missing | Low | Medium |

#### 8.3 In-App Notifications
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Real-time notifications | ⚠️ Partial | High | High |
| Notification center | ❌ Missing | Medium | Medium |
| Read/unread status | ❌ Missing | Medium | Low |
| Notification preferences | ❌ Missing | Low | Medium |
| Push notifications (web) | ❌ Missing | Medium | Medium |

#### 8.4 Communication Channels
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Internal messaging | ❌ Missing | Low | High |
| Chat support | ❌ Missing | Low | High |
| WhatsApp integration | ❌ Missing | Medium | High |
| Telegram bot | ❌ Missing | Low | High |

---

### 📱 **MODULE 9: MOBILE APPLICATIONS**

#### 9.1 Driver App
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Login/authentication | ❌ Missing | Critical | Low |
| Daily schedule view | ❌ Missing | Critical | Medium |
| Navigation to site | ❌ Missing | High | Medium |
| Delivery checklist | ❌ Missing | Critical | Medium |
| Photo capture | ❌ Missing | Critical | Low |
| Digital signature | ❌ Missing | Critical | Medium |
| Offline mode | ❌ Missing | High | High |
| GPS tracking | ❌ Missing | High | Medium |
| Delivery status updates | ❌ Missing | Critical | Medium |

#### 9.2 Technician App
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Maintenance work orders | ❌ Missing | High | Medium |
| Checklists | ❌ Missing | High | Medium |
| Parts logging | ❌ Missing | Medium | Medium |
| Time tracking | ❌ Missing | Medium | Medium |
| Photos/videos | ❌ Missing | High | Low |
| Fault code reader | ❌ Missing | Low | High |

#### 9.3 Client Mobile App
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| View bookings | ❌ Missing | High | Medium |
| Create booking requests | ❌ Missing | High | High |
| Track deliveries | ❌ Missing | Medium | High |
| View invoices | ❌ Missing | High | Low |
| Make payments | ❌ Missing | High | High |
| Support chat | ❌ Missing | Low | High |

---

### ⚙️ **MODULE 10: SYSTEM SETTINGS & CONFIGURATION**

#### 10.1 General Settings
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Company information | ✅ Exists | Critical | Low |
| Currency settings | ✅ Exists | Critical | Low |
| Date/time formats | ⚠️ Partial | High | Low |
| Tax rates configuration | ⚠️ Partial | Critical | Low |
| Email configuration | ⚠️ Partial | High | Medium |
| SMS configuration | ❌ Missing | High | Medium |

#### 10.2 Business Rules
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| Approval thresholds | ⚠️ Partial | High | Medium |
| Booking rules | ⚠️ Partial | High | Medium |
| Payment terms | ⚠️ Partial | High | Low |
| Pricing rules | ❌ Missing | High | High |
| Discount policies | ❌ Missing | Medium | Medium |
| Cancellation policies | ⚠️ Partial | High | Medium |

#### 10.3 Integration Settings
| Feature | Status | Priority | Complexity |
|---------|--------|----------|------------|
| API configuration | ⚠️ Partial | High | Medium |
| Payment gateway setup | ❌ Missing | High | High |
| SMS gateway setup | ❌ Missing | High | Medium |
| GPS platform integration | ⚠️ Partial | Medium | High |
| Accounting software sync | ❌ Missing | Medium | High |
| Webhook management | ❌ Missing | Low | High |

---

# 5. DATABASE SCHEMA DESIGN

## 5.1 Core Entities

### Users & Authentication
```
users
├─ id
├─ name
├─ email
├─ password
├─ role_id
├─ status (active/inactive/suspended)
├─ last_login_at
├─ login_attempts
├─ ip_address
├─ two_factor_secret
├─ two_factor_enabled
└─ timestamps

roles
├─ id
├─ name
├─ description
├─ permissions (JSON)
└─ timestamps

user_activity_logs
├─ id
├─ user_id
├─ action
├─ model_type
├─ model_id
├─ old_values (JSON)
├─ new_values (JSON)
├─ ip_address
└─ created_at
```

### Clients & CRM
```
clients
├─ id
├─ user_id (account owner)
├─ company_name
├─ tin_number
├─ vrn
├─ client_category_id
├─ credit_limit
├─ payment_terms_days
├─ credit_score
├─ payment_behavior_score
├─ status (active/inactive/blacklisted)
├─ risk_level (low/medium/high)
├─ profile_completion_percentage
└─ timestamps (with soft deletes)

client_contacts
├─ id
├─ client_id
├─ name
├─ position
├─ email
├─ phone
├─ is_primary
├─ can_authorize_bookings
├─ can_receive_invoices
└─ timestamps

client_addresses
├─ id
├─ client_id
├─ type (billing/shipping/service/office)
├─ street_address
├─ city
├─ region
├─ postal_code
├─ country
├─ is_default
├─ gps_coordinates (Point)
└─ timestamps

client_documents
├─ id
├─ client_id
├─ document_type
├─ file_path
├─ status (pending/approved/rejected/expired)
├─ expiry_date
├─ approved_by
├─ approved_at
└─ timestamps

quote_requests (Public Website Lead Capture)
├─ id
├─ request_number (auto: QR-2026-0001)
├─ full_name
├─ email
├─ phone
├─ company_name (optional)
├─ genset_model_id (FK to gensets table)
├─ requested_start_date
├─ rental_days
├─ delivery_location (address text)
├─ delivery_coordinates (Point - optional)
├─ pickup_location (address text)
├─ pickup_coordinates (Point - optional)
├─ additional_requirements (TEXT)
├─ status (new/reviewed/quoted/accepted/rejected/converted)
├─ source (website/phone/email/referral)
├─ ip_address
├─ user_agent
├─ reviewed_by (FK to users - null until reviewed)
├─ reviewed_at
├─ quotation_id (FK to quotations - null until quote created)
├─ booking_id (FK to bookings - null until converted)
├─ converted_at
├─ rejection_reason
└─ timestamps

contracts
├─ id
├─ contract_number
├─ client_id
├─ type (msa/rental/service)
├─ start_date
├─ end_date
├─ renewal_date
├─ status (draft/active/expired/cancelled)
├─ file_path
├─ terms (JSON)
└─ timestamps

quotations (Admin Generated Quotes)
├─ id
├─ quote_number
├─ quote_request_id (FK - can be null if manually created)
├─ client_id (FK - can be null for prospects)
├─ prospect_name (for prospects without client_id)
├─ prospect_email
├─ prospect_phone
├─ valid_until
├─ status (draft/sent/accepted/rejected/expired)
├─ items (JSON)
├─ subtotal
├─ tax_amount
├─ total_amount
├─ notes
├─ pdf_path
├─ sent_at
├─ accepted_at
├─ created_by (FK to users)
└─ timestamps
```

### Fleet/Gensets
```
gensets
├─ id
├─ genset_code
├─ name
├─ manufacturer
├─ model_number
├─ serial_number
├─ power_rating_kva
├─ fuel_type
├─ fuel_tank_capacity
├─ engine_make
├─ engine_model
├─ year_manufactured
├─ purchase_date
├─ purchase_price
├─ current_value
├─ daily_rate
├─ weekly_rate
├─ monthly_rate
├─ status (available/booked/rented/maintenance/retired)
├─ current_latitude
├─ current_longitude
├─ gps_device_id
├─ total_running_hours
├─ last_service_date
├─ next_service_due
├─ service_interval_hours
├─ qr_code
└─ timestamps (with soft deletes)

genset_maintenance_records
├─ id
├─ work_order_number
├─ genset_id
├─ type (preventive/corrective/emergency)
├─ scheduled_date
├─ completed_date
├─ technician_id
├─ status (scheduled/in_progress/completed/cancelled)
├─ work_description
├─ parts_used (JSON)
├─ labor_cost
├─ parts_cost
├─ total_cost
└─ timestamps

genset_location_logs
├─ id
├─ genset_id
├─ latitude
├─ longitude
├─ altitude
├─ speed
├─ heading
├─ accuracy
├─ logged_at
├─ movement_status (stationary/moving)
└─ created_at

genset_telemetry
├─ id
├─ genset_id
├─ running_status (on/off)
├─ running_hours
├─ fuel_level_percentage
├─ power_output_kw
├─ voltage
├─ frequency
├─ temperature
├─ load_percentage
├─ fault_codes (JSON)
├─ recorded_at
└─ created_at
```

### Bookings & Rentals
```
bookings
├─ id
├─ booking_code
├─ user_id (creator)
├─ client_id
├─ contact_person_id
├─ genset_id
├─ delivery_address_id
├─ billing_address_id
├─ hiring_date
├─ return_date
├─ actual_return_date
├─ days
├─ overdue_days
├─ quantity
├─ rate
├─ base_amount
├─ fuel_charges
├─ transport_charges
├─ lift_charges
├─ other_charges
├─ subtotal
├─ discount_amount
├─ tax_amount
├─ total_cost
├─ status (draft/pending/approved/active/delivered/returned/completed/cancelled/overdue)
├─ payment_status (pending/partial/paid/overdue/refunded)
├─ delivery_status (pending/scheduled/in_transit/delivered/returned)
├─ approved_by
├─ approved_at
├─ special_requirements (TEXT)
├─ fuel_level_delivered
├─ fuel_level_returned
├─ condition_delivered
├─ condition_returned
├─ delivery_photos (JSON)
├─ return_photos (JSON)
└─ timestamps (with soft deletes)

booking_logs
├─ id
├─ booking_id
├─ user_id
├─ action
├─ old_status
├─ new_status
├─ notes
└─ created_at
```

### Deliveries
```
deliveries
├─ id
├─ delivery_number
├─ booking_id
├─ type (delivery/pickup)
├─ driver_id
├─ vehicle_id
├─ scheduled_date
├─ actual_date
├─ status (pending/scheduled/in_transit/completed/cancelled)
├─ departure_time
├─ arrival_time
├─ route (LineString)
├─ distance_km
├─ notes
└─ timestamps

delivery_checkpoints
├─ id
├─ delivery_id
├─ checkpoint_type (departure/arrival/waypoint)
├─ location (Point)
├─ timestamp
├─ notes
└─ created_at

proof_of_delivery
├─ id
├─ delivery_id
├─ recipient_name
├─ recipient_signature (file)
├─ photos (JSON)
├─ condition_checklist (JSON)
├─ fuel_level
├─ running_hours
├─ notes
├─ gps_coordinates (Point)
├─ confirmed_at
└─ created_at
```

### Financial
```
invoices
├─ id
├─ invoice_number
├─ booking_id
├─ client_id
├─ issue_date
├─ due_date
├─ items (JSON)
├─ subtotal
├─ discount_amount
├─ vat_amount (18%)
├─ wht_amount
├─ total_amount
├─ amount_paid
├─ balance
├─ status (draft/sent/partial/paid/overdue/cancelled)
├─ payment_terms
├─ notes
├─ tra_receipt_number
└─ timestamps

payments
├─ id
├─ payment_number
├─ invoice_id
├─ client_id
├─ amount
├─ payment_method (cash/bank_transfer/mpesa/tigo/airtel/cheque)
├─ payment_date
├─ reference_number
├─ status (pending/verified/failed/refunded)
├─ verified_by
├─ verified_at
├─ notes
└─ timestamps

payment_logs
├─ id
├─ payment_id
├─ action
├─ old_status
├─ new_status
├─ user_id
├─ notes
└─ created_at

refunds
├─ id
├─ refund_number
├─ payment_id
├─ invoice_id
├─ amount_requested
├─ amount_approved
├─ reason
├─ status (requested/approved/rejected/processed)
├─ requested_by
├─ approved_by
├─ processed_at
└─ timestamps

chart_of_accounts
├─ id
├─ account_code
├─ account_name
├─ account_type (asset/liability/equity/revenue/expense)
├─ parent_id
├─ is_active
└─ timestamps

journal_entries
├─ id
├─ entry_number
├─ entry_date
├─ description
├─ reference_type (invoice/payment/expense)
├─ reference_id
├─ created_by
└─ timestamps

journal_entry_lines
├─ id
├─ journal_entry_id
├─ account_id
├─ debit_amount
├─ credit_amount
├─ description
└─ created_at
```

### Notifications
```
notifications
├─ id
├─ user_id
├─ type
├─ title
├─ message
├─ data (JSON)
├─ read_at
├─ action_url
└─ created_at

notification_templates
├─ id
├─ name
├─ type (email/sms/push)
├─ subject
├─ body
├─ variables (JSON)
└─ timestamps
```

## 5.2 Indexes & Optimization

**Critical Indexes:**
```sql
-- Users
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role_id);

-- Clients
CREATE INDEX idx_clients_tin ON clients(tin_number);
CREATE INDEX idx_clients_vrn ON clients(vrn);
CREATE INDEX idx_clients_category ON clients(client_category_id);
CREATE INDEX idx_clients_status ON clients(status);

-- Gensets
CREATE INDEX idx_gensets_status ON gensets(status);
CREATE INDEX idx_gensets_gps ON gensets(current_latitude, current_longitude);
CREATE SPATIAL INDEX idx_gensets_location ON gensets(gps_coordinates);

-- Bookings
CREATE INDEX idx_bookings_client ON bookings(client_id);
CREATE INDEX idx_bookings_genset ON bookings(genset_id);
CREATE INDEX idx_bookings_status ON bookings(status);
CREATE INDEX idx_bookings_dates ON bookings(hiring_date, return_date);
CREATE INDEX idx_bookings_payment_status ON bookings(payment_status);

-- Invoices
CREATE INDEX idx_invoices_client ON invoices(client_id);
CREATE INDEX idx_invoices_status ON invoices(status);
CREATE INDEX idx_invoices_due_date ON invoices(due_date);

-- Performance
CREATE INDEX idx_genset_telemetry_genset_time ON genset_telemetry(genset_id, recorded_at);
CREATE INDEX idx_location_logs_genset_time ON genset_location_logs(genset_id, logged_at);
```

---

# 6. USER ROLES & PERMISSIONS

## 6.1 Permission Matrix

| Module | Super Admin | Admin | Finance Mgr | Ops Mgr | Sales Mgr | Dispatcher | Driver | Tech | Client |
|--------|:-----------:|:-----:|:-----------:|:-------:|:---------:|:----------:|:------:|:----:|:------:|
| **Users** |
| Create users | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Edit users | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Delete users | ✅ | ⚠️ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Assign roles | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Clients** |
| View clients | ✅ | ✅ | ✅ | ✅ | ✅ | 👁️ | ❌ | ❌ | ⚠️ |
| Create clients | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Edit clients | ✅ | ✅ | ⚠️ | ❌ | ✅ | ❌ | ❌ | ❌ | ⚠️ |
| Delete clients | ✅ | ⚠️ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Fleet** |
| View fleet | ✅ | ✅ | 👁️ | ✅ | 👁️ | ✅ | 👁️ | ✅ | ❌ |
| Add equipment | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Edit equipment | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ | ⚠️ | ❌ |
| Delete equipment | ✅ | ⚠️ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Bookings** |
| View all bookings | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | 👁️ | ❌ | ⚠️ |
| Create booking | ✅ | ✅ | ❌ | ✅ | ✅ | ❌ | ❌ | ❌ | ⚠️ |
| Edit booking | ✅ | ✅ | ⚠️ | ✅ | ⚠️ | ⚠️ | ❌ | ❌ | ❌ |
| Cancel booking | ✅ | ✅ | ❌ | ✅ | ⚠️ | ❌ | ❌ | ❌ | ⚠️ |
| Approve booking | ✅ | ✅ | ⚠️ | ✅ | ⚠️ | ❌ | ❌ | ❌ | ❌ |
| **Deliveries** |
| View deliveries | ✅ | ✅ | 👁️ | ✅ | 👁️ | ✅ | ⚠️ | ❌ | 👁️ |
| Create delivery | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| Assign driver | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| Complete delivery | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ | ✅ | ❌ | ❌ |
| **Maintenance** |
| View maintenance | ✅ | ✅ | 👁️ | ✅ | ❌ | 👁️ | ❌ | ✅ | ❌ |
| Schedule maintenance | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ | ⚠️ | ❌ |
| Complete maintenance | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ | ✅ | ❌ |
| **Financial** |
| View invoices | ✅ | ✅ | ✅ | 👁️ | 👁️ | ❌ | ❌ | ❌ | ⚠️ |
| Create invoice | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Edit invoice | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Record payment | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ⚠️ |
| Process refund | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| View reports | ✅ | ✅ | ✅ | ✅ | ✅ | 👁️ | ❌ | 👁️ | 👁️ |
| **Settings** |
| System settings | ✅ | ⚠️ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Business rules | ✅ | ✅ | ⚠️ | ⚠️ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Integration config | ✅ | ⚠️ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |

**Legend:**
- ✅ Full access
- ⚠️ Limited/conditional access
- 👁️ View only
- ❌ No access

---

# 7. DEVELOPMENT PHASES

## REVISED DEVELOPMENT ROADMAP (March 2026)

**Critical Realization**: Before building features, we must establish the visual foundation that represents Milele Power's brand identity.

---

## Phase 0: DESIGN FOUNDATION (Week 1-2) 🎨
**Goal**: Establish Milele Power brand identity throughout the system
**Priority**: CRITICAL - Must complete before any feature development

### Sprint 0: Design System Implementation
**Duration**: 2 weeks  
**Team**: Frontend Developer + Designer

#### Tasks:
- [x] Analyze existing website (milelepower.co.tz) ✅ COMPLETED
- [ ] Configure Tailwind CSS with Milele Power color system
  - Primary: Black (#000000, #1a1a1a)
  - Accent: Red (#DC2626, #B91C1C, #EF4444)
  - Supporting: Grays and Whites
- [ ] Create Tailwind configuration file (tailwind.config.js)
  - Custom color palette
  - Typography scale
  - Spacing and sizing
  - Custom components
- [ ] Build component library
  - Buttons (Primary Red, Secondary Black, Ghost)
  - Forms (inputs, selects, checkboxes with red focus)
  - Cards (white with red accent)
  - Tables (black headers, red actions)
  - Status badges (color-coded)
  - Navigation (black navbar, black sidebar)
  - Modals and overlays
- [ ] Create base layout templates
  - Auth layout (login/register with branding)
  - Admin layout (black sidebar + navbar)
  - Public layout (landing page style)
- [ ] Design authentication pages
  - Login page (black & red theme)
  - Registration page
  - Password reset
  - 2FA (future)
- [ ] Create reusable Blade components
  - `<x-button>` (multiple variants)
  - `<x-input>` (with red focus states)
  - `<x-card>` (with optional red accent)
  - `<x-badge>` (status colors)
  - `<x-table>` (sortable with black headers)
  - `<x-modal>`
  - `<x-sidebar>`
  - `<x-navbar>`
- [ ] Implement dashboard shell
  - Black sidebar navigation
  - Black top navbar
  - KPI card templates (red accents)
  - Chart component placeholders
- [ ] Create style guide document
  - Color usage guidelines
  - Typography standards
  - Component examples
  - Do's and Don'ts
- [ ] Build public landing page
  - Hero section (dark with red CTA)
  - Services section (6 cards)
  - Target clients section
  - Stats section (1000+ rentals)
  - Contact section (black background)
  - Footer (black)
- [ ] **NEW: Build Quote Request Form**
  - "Get Quote" CTA button (prominent, red)
  - Quote request form (modal or page)
  - Form fields (name, email, phone, genset, dates, locations)
  - Genset model dropdown (live data from DB)
  - Date pickers for start date and duration
  - Location input fields (delivery & pickup)
  - Google Maps integration (optional Phase 1)
  - Form validation (client + server side)
  - CAPTCHA integration (spam protection)
  - Success message/page
  - Auto-reply email to prospect
- [ ] **NEW: Quote Request Backend**
  - Create database migration for quote_requests table
  - Create QuoteRequest model
  - Create QuoteRequestController
  - Form submission endpoint (POST)
  - Email notification to admin on new request
  - Auto-generate request number (QR-YYYY-####)
  - Rate limiting middleware
- [ ] **NEW: Admin Quote Request Dashboard**
  - List all quote requests (table view)
  - Status filter (new/reviewed/quoted/converted)
  - View request details page
  - Mark as reviewed action
  - Basic quote request stats

**Deliverable**: 
- ✅ Complete Milele Power branded design system
- ✅ Reusable component library
- ✅ Authentication pages with black & red theme
- ✅ Dashboard shell with proper layout
- ✅ Landing page matching website style
- ✅ **Quote request form (fully functional)**
- ✅ **Quote request backend (database + notifications)**
- ✅ **Admin panel for managing quote requests**
- ✅ Style guide documentation

**Success Criteria**:
- All colors use official Milele Power palette
- Consistent spacing and typography
- Mobile responsive (all breakpoints)
- Accessibility standards met
- Fast load times (<2s)
- **Quote form captures leads successfully**
- **Admin receives email notifications**
- **Quote requests visible in admin panel**

**TIMELINE EXTENSION**: Phase 0 extended to **3-4 weeks** due to quote request system addition.

---

## Phase 1: FOUNDATION (Months 1-3)
**Goal**: Implement quotation generation & conversion, stabilize system

### Sprint 1-2: Quotation System & Conversion Flow
**Duration**: 2 weeks  
**Dependencies**: Phase 0 (quote requests) complete
**CRITICAL**: This enables the prospect → customer conversion

- [ ] **Quotation Generation from Quote Requests**
  - Create database migration for updated quotations table
  - Quotation model with quote_request relationship
  - Generate quotation from quote request (UI)
  - Quotation builder (items, pricing, taxes)
  - PDF generation (branded template)
  - Email quotation to prospect
  - Track quotation status (sent/viewed/accepted/rejected)
- [ ] **Prospect to Customer Conversion**
  - "Convert to Booking" workflow
  - Create client record from prospect data
  - Create booking from accepted quotation
  - Link quote_request → quotation → booking
  - Track conversion metrics
- [ ] **Quotation Management Interface**
  - View all quotations
  - Edit draft quotations
  - Resend quotations
  - Quotation approval workflow (if needed)
  - Quotation templates
- [ ] **Deliverable**: Complete Quote Request → Quotation → Booking flow

### Sprint 3-4: System Upgrade & Stability
**Duration**: 2 weeks  
**Dependencies**: Design system complete

- [ ] Complete Laravel 12 upgrade
- [ ] Fix all breaking changes
- [ ] Update all dependencies (Composer + NPM)
- [ ] Apply new design system to existing views
- [ ] Migrate old Bootstrap/CSS to Tailwind
- [ ] Comprehensive testing
- [ ] Security audit
- [ ] **Deliverable**: Stable Laravel 12 system with Milele Power branding

### Sprint 5-6: Fuel Management System
**Duration**: 2 weeks

- [ ] Fuel tracking database schema
- [ ] Fuel level monitoring UI (with charts)
- [ ] Fuel delivered vs returned comparison
- [ ] Fuel cost calculations
- [ ] Fuel theft alerts (red notifications)
- [ ] Fuel reports (with red accents on critical data)
- [ ] **Deliverable**: Complete fuel management module

### Sprint 7-8: TRA Compliance & Tax
**Duration**: 2 weeks  
**Critical for Tanzania operations**

- [ ] TRA EFD/VFD API integration
- [ ] VAT invoice generation (18%)
- [ ] VRN/TIN validation
- [ ] Electronic receipts
- [ ] Z-reports
- [ ] WHT calculations (2%, 5%, 10%)
- [ ] Tax reports with proper formatting
- [ ] **Deliverable**: TRA compliant invoicing system

### Sprint 7-8: Proof of Delivery Enhancement
**Duration**: 2 weeks

- [ ] Digital signature capture (improved, mobile-friendly)
- [ ] Enhanced photo documentation (multiple angles)
- [ ] Digital condition checklists
- [ ] GPS-stamped confirmations
- [ ] POD PDF generation (branded)
- [ ] Return inspections
- [ ] Damage documentation workflow
- [ ] **Deliverable**: Complete POD system

### Sprint 9-10: Credit Management
**Duration**: 2 weeks

- [ ] Credit scoring system
- [ ] Payment behavior tracking
- [ ] Automated payment reminders (email + SMS)
- [ ] Dunning process (escalation workflow)
- [ ] Aging reports (color-coded: red for overdue)
- [ ] Account holds (red alerts)
- [ ] Credit limit management
- [ ] **Deliverable**: Credit control system

### Sprint 11-12: Comprehensive Invoicing & Quotations
**Duration**: 2 weeks

- [ ] Quotation system (with workflow)
- [ ] Quote templates (professionally branded)
- [ ] Quote approval workflow
- [ ] Quote to booking conversion
- [ ] Proforma invoices
- [ ] Credit notes (refunds)
- [ ] Debit notes
- [ ] Invoice approval workflow
- [ ] Recurring invoices
- [ ] Statement of accounts
- [ ] **Deliverable**: Complete invoicing & quotation system

## Phase 2: GROWTH (Months 4-6)
**Goal**: Add automation and intelligence

### Sprint 13-14: IoT Integration
- [ ] Telemetry data integration
- [ ] Real-time genset monitoring
- [ ] Fault code detection
- [ ] Alert system
- [ ] Performance dashboard
- [ ] **Deliverable**: IoT monitoring system

### Sprint 15-16: Contract Management
- [ ] Contract creation & storage
- [ ] MSA management
- [ ] Rate cards
- [ ] Renewal tracking
- [ ] SLA monitoring
- [ ] **Deliverable**: Contract management

### Sprint 17-18: CRM & Sales Pipeline
- [ ] Lead management
- [ ] Opportunity tracking
- [ ] Quote approval
- [ ] Quote-to-booking conversion
- [ ] Sales analytics
- [ ] **Deliverable**: Complete CRM

### Sprint 19-20: Advanced Reporting
- [ ] P&L statements
- [ ] Balance sheet
- [ ] Cash flow
- [ ] Custom reports
- [ ] Export to Excel/PDF
- [ ] **Deliverable**: Financial reporting

### Sprint 21-22: Mobile App (Driver)
- [ ] iOS/Android app
- [ ] Daily schedule
- [ ] Navigation
- [ ] Delivery checklist
- [ ] Photo/signature capture
- [ ] Offline mode
- [ ] **Deliverable**: Driver mobile app

### Sprint 23-24: Mobile App (Technician)
- [ ] Work order management
- [ ] Checklists
- [ ] Time tracking
- [ ] Parts logging
- [ ] **Deliverable**: Technician mobile app

## Phase 3: SCALE (Months 7-12)
**Goal**: Enterprise features and optimization

### Sprint 25-26: Payment Gateway Integration
- [ ] M-Pesa integration
- [ ] Tigo Pesa integration
- [ ] Airtel Money integration
- [ ] Bank transfers
- [ ] Payment reconciliation
- [ ] **Deliverable**: Online payments

### Sprint 27-28: Multi-Location Support
- [ ] Branch management
- [ ] Inter-branch transfers
- [ ] Location-based pricing
- [ ] Centralized reporting
- [ ] **Deliverable**: Multi-branch operations

### Sprint 29-30: Advanced Analytics
- [ ] Predictive analytics
- [ ] Demand forecasting
- [ ] Price optimization
- [ ] Utilization insights
- [ ] **Deliverable**: Business intelligence

### Sprint 31-32: Client Self-Service Portal
- [ ] Client dashboard
- [ ] Online booking
- [ ] Invoice access
- [ ] Payment portal
- [ ] Document center
- [ ] **Deliverable**: Client portal

### Sprint 33-34: Asset Lifecycle Management
- [ ] Depreciation tracking
- [ ] Disposal planning
- [ ] ROI analysis
- [ ] Replacement planning
- [ ] **Deliverable**: Asset management

### Sprint 35-36: API & Integrations
- [ ] REST API development
- [ ] API documentation
- [ ] Third-party integrations
- [ ] Webhook system
- [ ] **Deliverable**: API platform

## Phase 4: OPTIMIZATION (Ongoing)
- Performance tuning
- Security hardening
- User experience improvements
- Bug fixes
- Feature enhancements
- Training materials
- Documentation

---

# 8. TECHNICAL STACK (DETAILED)

## 8.1 Backend Stack

### Framework & Language
```yaml
Framework: Laravel 12.x
PHP Version: 8.2+
Architecture: MVC + Service Layer
Design Patterns:
  - Repository Pattern (data access)
  - Service Pattern (business logic)
  - Observer Pattern (events)
  - Factory Pattern (object creation)
  - Strategy Pattern (payment methods, notifications)
```

### Database
```yaml
Primary: MySQL 8.0+
  - InnoDB engine
  - UTF8MB4 charset
  - Full-text search indexes
  - Spatial data types (GPS)
  
Cache: Redis 7.0+
  - Session storage
  - Cache storage
  - Queue driver
  - Pub/Sub for real-time updates
  
Search: Meilisearch / Elasticsearch
  - Full-text search
  - Faceted search
  - Real-time indexing
```

### Queue & Jobs
```yaml
Driver: Redis
Use Cases:
  - Email sending
  - SMS notifications
  - Report generation
  - Data import/export
  - Image processing
  - PDF generation
  - API webhooks
```

### File Storage
```yaml
Local: storage/app/
  - Development environment
  - Temporary files
  
Cloud: AWS S3 / DigitalOcean Spaces
  - Production files
  - Document storage
  - Image/photo storage
  - Backup storage
  
CDN: CloudFlare / AWS CloudFront
  - Static assets
  - Public images
  - Faster delivery
```

## 8.2 Frontend Stack

### Admin Panel
```yaml
Template Engine: Laravel Blade
JavaScript: Alpine.js 3.x
  - Reactive components
  - No build step required
  - Lightweight (15kb)
  
CSS Framework: Tailwind CSS 3.x
  - Utility-first
  - Custom design system
  - Dark mode support
  
UI Components:
  - Custom component library
  - Reusable Blade components
  - Icon library: Boxicons/Heroicons
  
Charts: Chart.js / ApexCharts
  - Interactive charts
  - Real-time updates
  - Export capabilities
```

### Client Portal (SPA)
```yaml
Framework: Vue.js 3 / React 18
State Management: Vuex / Redux / Pinia
HTTP Client: Axios
Build Tool: Vite 5
UI Library: Vuetify / Ant Design / Material UI
```

### Mobile Apps
```yaml
Framework: Flutter 3.x
  - Single codebase
  - iOS & Android
  - Native performance
  - Hot reload
  
Alternative: React Native
  - JavaScript/TypeScript
  - Large ecosystem
  - Code sharing with web
```

## 8.3 DevOps & Infrastructure

### Server Environment
```yaml
OS: Ubuntu 22.04 LTS
Web Server: Nginx 1.22+
PHP: PHP-FPM 8.2+
Database: MySQL 8.0+
Cache: Redis 7.0+
Supervisor: Process management for queues
```

### Containerization
```yaml
Docker:
  - Development environment
  - Consistent deployment
  - Service isolation
  
Docker Compose:
  - Multi-container orchestration
  - Local development setup
```

### CI/CD Pipeline
```yaml
Version Control: Git
Repository: GitHub / GitLab
CI/CD Tool: GitHub Actions / GitLab CI

Pipeline Stages:
  1. Code checkout
  2. Dependency installation
  3. Code quality checks (PHP CS Fixer, Pint)
  4. Security scanning
  5. Unit tests
  6. Feature tests
  7. Build assets
  8. Deploy to staging
  9. Integration tests
  10. Deploy to production
```

### Monitoring & Logging
```yaml
Application Monitoring:
  - Laravel Telescope (development)
  - Laravel Horizon (queue monitoring)
  
Error Tracking:
  - Sentry.io
  - Bugsnag
  
Log Management:
  - ELK Stack (Elasticsearch, Logstash, Kibana)
  - AWS CloudWatch (if using AWS)
  
Performance Monitoring:
  - New Relic / DataDog
  - Application Performance Monitoring (APM)
  
Uptime Monitoring:
  - UptimeRobot
  - Pingdom
```

### Security
```yaml
SSL/TLS: Let's Encrypt (free) / Commercial
Firewall: UFW (Uncomplicated Firewall)
Intrusion Detection: Fail2Ban
DDoS Protection: CloudFlare
Backup:
  - Database: Daily automated backups
  - Files: Incremental backups
  - Off-site storage: AWS S3 / Backblaze B2
```

---

# 9. INTEGRATION REQUIREMENTS

## 9.1 Payment Gateways

### M-Pesa (Vodacom Tanzania)
```yaml
API Type: REST API
Authentication: OAuth 2.0
Features Needed:
  - C2B (Customer to Business)
  - B2C (Business to Customer - refunds)
  - Transaction status query
  - Payment confirmation webhooks
  
Documentation: https://developer.vodacom.co.tz/
```

### Tigo Pesa
```yaml
API Type: REST API
Features Needed:
  - Payment collection
  - Payment disbursement
  - Transaction inquiry
  - Balance inquiry
```

### Airtel Money
```yaml
API Type: REST API
Features Needed:
  - Payment push
  - Transaction status
  - Balance check
```

### Bank Integration
```yaml
SWIFT Integration: For corporate transfers
Bank Reconciliation: MT940 file import
```

## 9.2 SMS Gateway

### Africa's Talking (Recommended for Tanzania)
```yaml
Services:
  - SMS (single/bulk)
  - USSD (optional)
  - Voice (optional)
  
Endpoints:
  - Send SMS
  - Receive SMS (webhooks)
  - Delivery reports
  - Balance inquiry
  
Pricing: ~0.04 USD per SMS (Tanzania)
```

### Alternative: Twilio
```yaml
More expensive but better infrastructure
Global coverage
Advanced features (Verify API for OTP)
```

## 9.3 Email Service

### SendGrid
```yaml
Features:
  - Transactional emails
  - Marketing emails
  - Email templates
  - Delivery tracking
  - Click/open tracking
  
Free Tier: 100 emails/day
Paid: Starting $19.95/month
```

### Alternative: AWS SES
```yaml
More cost-effective at scale
$0.10 per 1,000 emails
Requires more setup
```

## 9.4 Maps & Geolocation

### Google Maps Platform
```yaml
APIs Needed:
  - Maps JavaScript API (interactive maps)
  - Directions API (routing)
  - Distance Matrix API (distance calculations)
  - Geocoding API (address to coordinates)
  - Places API (location search)
  
Pricing: $7-$40 per 1000 requests
Free Tier: $200/month credit
```

### Alternative: OpenStreetMap + MapBox
```yaml
More cost-effective
Open source
Good for Africa coverage
```

## 9.5 IoT/Telematics Platform

### Genset Monitoring Options

#### Option 1: DeepSea Electronics
```yaml
Product: DSE WebNet
Features:
  - Real-time monitoring
  - Fault alerts
  - Running hours
  - Power output
  - Fuel levels (with sensor)
API: REST API available
```

#### Option 2: Custom IoT Solution
```yaml
Hardware: Raspberry Pi / Arduino + 4G modem
Sensors:
  - GPS tracker
  - Fuel level sensor
  - Temperature sensor
  - Voltage/current sensors
  
Communication: MQTT protocol
Platform: AWS IoT Core / Google Cloud IoT
```

## 9.6 TRA Integration (Tanzania Revenue Authority)

### Electronic Fiscal Device (EFD/VFD)
```yaml
Implementation Options:
  1. EFD Hardware Device
     - Physical device connected to system
     - Prints receipts
     - Cost: ~$200-500 per device
  
  2. Virtual Fiscal Device (VFD)
     - Software-based solution
     - No physical device
     - API integration
     - Must be pre-approved by TRA
  
Requirements:
  - VAT registration
  - TRA approval
  - Compliance with specifications
  - Z-report generation
```

### TRA Online Services
```yaml
Services:
  - VRN validation
  - TIN validation
  - Tax return submission
  - Payment confirmation
```

## 9.7 Accounting Software Integration

### QuickBooks
```yaml
API Type: REST API
OAuth 2.0 authentication
Features:
  - Sync invoices
  - Sync payments
  - Sync customers
  - Chart of accounts
```

### Xero
```yaml
Similar to QuickBooks
Better suited for smaller businesses
Good API documentation
```

---

# 10. UI/UX DESIGN GUIDELINES

## 10.1 Brand Identity & Design Principles

### Brand Alignment
**CRITICAL**: All UI/UX design MUST align with Milele Power's established brand identity from https://www.milelepower.co.tz

**Brand Personality**:
- Professional and powerful (represented by **Black**)
- Energetic and action-oriented (represented by **Red**)
- Reliable and trustworthy
- Modern and innovative
- Customer-focused

### Core Design Principles

#### 1. Brand Consistency First
- Maintain black and red color scheme throughout
- Reflect company values: Quality, Reliability, Excellence
- Professional appearance suitable for B2B cold chain industry
- Trust-building visual elements

#### 2. Clarity Over Complexity
- Clean, uncluttered interfaces
- Clear call-to-actions with red accent
- Consistent terminology from website ("Genset", "Rental", "Cold Chain")
- Logical information hierarchy

#### 3. Efficiency for Power Users
- Keyboard shortcuts
- Bulk operations
- Global search (prominent in black header)
- Recent items/quick actions
- Favorites/bookmarks

#### 4. Mobile-First Responsive
- Works on all devices (desktop, tablet, mobile)
- Touch-friendly controls (min 44x44px touch targets)
- Readable typography at all sizes
- Optimized images and icons

#### 5. Accessibility
- WCAG 2.1 Level AA compliance
- High contrast (black/red/white naturally provides good contrast)
- Keyboard navigation
- Screen reader support
- Alternative text for all images
- Focus indicators clearly visible

---

## 10.2 Official Milele Power Design System

### Color Palette (MANDATORY)

#### Primary Colors
```yaml
# Black Shades (Primary Brand Color)
Black-900: #000000          # Header, sidebar, primary text, brand elements
Black-800: #1a1a1a          # Hover states, card headers
Black-700: #262626          # Borders, dividers
Black-600: #404040          # Secondary text

# Red Shades (Accent Brand Color)
Red-600: #DC2626           # Primary CTA buttons, active states, links
Red-700: #B91C1C           # Hover states for red buttons
Red-500: #EF4444           # Alerts, notifications, badges
Red-100: #FEE2E2           # Light backgrounds, highlights
Red-50: #FEF2F2            # Very light backgrounds

# Neutral Colors (Supporting)
White: #FFFFFF             # Card backgrounds, content areas
Gray-50: #F9FAFB          # Page backgrounds
Gray-100: #F3F4F6         # Subtle backgrounds
Gray-200: #E5E7EB         # Borders
Gray-300: #D1D5DB         # Dividers
Gray-400: #9CA3AF         # Placeholder text
Gray-500: #6B7280         # Secondary text
Gray-600: #4B5563         # Body text

# Status Colors (Functional)
Success-Green: #10B981     # Available, completed, success states
Warning-Amber: #F59E0B     # Pending, attention needed
Error-Red: #DC2626         # Errors, overdue, critical (uses brand red)
Info-Blue: #3B82F6         # Informational messages
```

#### Color Usage Guidelines
```yaml
Backgrounds:
  - Page Background: Gray-50
  - Card Background: White
  - Header/Navbar: Black-900
  - Sidebar: Black-900
  - Footer: Black-900
  - Section Highlights: Red-50

Text:
  - Primary Text: Black-900
  - Secondary Text: Gray-600
  - Muted Text: Gray-500
  - Text on Dark BG: White
  - Links: Red-600 (hover: Red-700)
  - Headings: Black-900

Buttons:
  - Primary: Red-600 BG, White text
  - Primary Hover: Red-700 BG
  - Secondary: Black-900 outline, Black-900 text
  - Secondary Hover: Black-900 BG, White text
  - Danger: Red-600 BG
  - Ghost: Transparent BG, Red-600 text

Borders & Dividers:
  - Default: Gray-200
  - Focus: Red-600
  - Error: Red-500

Status Indicators:
  - Available/Success: Success-Green
  - Pending/Warning: Warning-Amber
  - Booked/Active/Critical: Red-500
  - Maintenance/Inactive: Gray-400
  - Overdue/Error: Red-600
```

### Typography

#### Font Families
```yaml
Primary Font: 'Inter', sans-serif
  - Modern, professional, excellent readability
  - Use for all UI text, headings, body text
  
Secondary/Display Font: 'Poppins', sans-serif (alternative)
  - Slightly more friendly while maintaining professionalism
  
Monospace Font: 'JetBrains Mono', monospace
  - For codes, IDs, technical data (booking numbers, TIN, VRN)
```

#### Font Sizes & Weights
```yaml
Font Sizes (Responsive):
  xs: 0.75rem (12px)      # Labels, captions, meta data
  sm: 0.875rem (14px)     # Secondary text, table cells
  base: 1rem (16px)       # Body text, form inputs
  lg: 1.125rem (18px)     # Emphasized text
  xl: 1.25rem (20px)      # Card titles, section headers
  2xl: 1.5rem (24px)      # Page sub-headers
  3xl: 1.875rem (30px)    # Page headers
  4xl: 2.25rem (36px)     # Dashboard titles
  5xl: 3rem (48px)        # Hero text

Font Weights:
  normal: 400             # Body text
  medium: 500             # Subtle emphasis
  semibold: 600           # Buttons, important text
  bold: 700               # Headings, strong emphasis
  extrabold: 800          # Hero headings, major CTAs

Line Heights:
  tight: 1.25             # Headings
  normal: 1.5             # Body text
  relaxed: 1.75           # Long-form content
```

#### Typography Usage
```yaml
Page Title: 3xl, Bold, Black-900
Section Heading: 2xl, Semibold, Black-900
Card Title: xl, Semibold, Black-900
Body Text: base, Normal, Gray-600
Label: sm, Medium, Gray-700
Caption: xs, Normal, Gray-500
CTA Text: base, Semibold, White (on red button)
```

### Spacing Scale
```yaml
Spacing (Tailwind Compatible):
  0: 0px
  px: 1px
  0.5: 0.125rem (2px)
  1: 0.25rem (4px)
  1.5: 0.375rem (6px)
  2: 0.5rem (8px)
  2.5: 0.625rem (10px)
  3: 0.75rem (12px)
  3.5: 0.875rem (14px)
  4: 1rem (16px)
  5: 1.25rem (20px)
  6: 1.5rem (24px)
  7: 1.75rem (28px)
  8: 2rem (32px)
  9: 2.25rem (36px)
  10: 2.5rem (40px)
  12: 3rem (48px)
  16: 4rem (64px)
  20: 5rem (80px)
  24: 6rem (96px)

Component Spacing Guidelines:
  - Card Padding: 5 or 6 (20-24px)
  - Section Margin: 8 or 12 (32-48px)
  - Button Padding: py-2 px-4 (8px 16px)
  - Form Field Gap: 4 or 6 (16-24px)
  - Content Max Width: 1280px
```

### Border Radius
```yaml
Border Radius:
  none: 0
  sm: 0.125rem (2px)
  DEFAULT: 0.25rem (4px)
  md: 0.375rem (6px)
  lg: 0.5rem (8px)
  xl: 0.75rem (12px)
  2xl: 1rem (16px)
  3xl: 1.5rem (24px)
  full: 9999px (circles)

Component Usage:
  - Buttons: md (6px)
  - Cards: lg (8px)
  - Modals: xl (12px)
  - Inputs: md (6px)
  - Badges: full (pill shape)
```

### Shadows & Elevation
```yaml
Box Shadows (Elevation Levels):
  sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05)
  DEFAULT: 0 1px 3px 0 rgba(0, 0, 0, 0.1)
  md: 0 4px 6px -1px rgba(0, 0, 0, 0.1)
  lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1)
  xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1)
  2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25)

Usage:
  - Cards: DEFAULT or md
  - Dropdowns: lg
  - Modals: xl or 2xl
  - Floating Elements: lg
  - Buttons (hover): md
```

---

## 10.3 Component Library

### Buttons

#### Primary Button (Red - Main CTA)
```html
<button class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-md transition-colors duration-200">
  Get Started
</button>
```

#### Secondary Button (Black Outline)
```html
<button class="border-2 border-black-900 text-black-900 hover:bg-black-900 hover:text-white font-semibold py-2 px-4 rounded-md transition-all duration-200">
  Cancel
</button>
```

#### Ghost Button
```html
<button class="text-red-600 hover:bg-red-50 font-medium py-2 px-4 rounded-md">
  Learn More
</button>
```

#### Icon Button
```html
<button class="p-2 text-gray-600 hover:text-red-600 hover:bg-gray-100 rounded-md">
  <icon />
</button>
```

### Forms & Inputs

#### Text Input
```html
<input class="w-full px-4 py-2 border border-gray-200 rounded-md focus:ring-2 focus:ring-red-600 focus:border-transparent" />
```

#### Select Dropdown
```html
<select class="w-full px-4 py-2 border border-gray-200 rounded-md focus:ring-2 focus:ring-red-600">
  <option>Select...</option>
</select>
```

#### Checkbox & Radio
- Custom styled with red accent color
- Black border, red fill when checked

### Cards

#### Standard Card
```html
<div class="bg-white rounded-lg shadow-md p-6">
  <h3 class="text-xl font-semibold text-black-900 mb-2">Card Title</h3>
  <p class="text-gray-600">Card content...</p>
</div>
```

#### Card with Red Accent
```html
<div class="bg-white rounded-lg shadow-md border-t-4 border-red-600 p-6">
  <h3 class="text-xl font-semibold text-black-900 mb-2">Featured Card</h3>
  <p class="text-gray-600">Content...</p>
</div>
```

#### KPI/Stats Card
```html
<div class="bg-white rounded-lg shadow-md p-6">
  <div class="flex items-center justify-between">
    <div>
      <p class="text-sm text-gray-500">Total Revenue</p>
      <p class="text-3xl font-bold text-black-900">1,234</p>
    </div>
    <div class="p-3 bg-red-100 rounded-full">
      <icon class="text-red-600" />
    </div>
  </div>
</div>
```

### Data Tables

#### Table Style
```yaml
Table Container: White background, shadow-md
Table Header: Black-900 background, white text
Table Rows: White background, hover:Gray-50
Table Borders: Gray-200
Action Buttons: Red-600 for primary actions
Pagination: Red-600 for active page
```

### Status Badges

#### Badge Colors
```html
<!-- Available/Success -->
<span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
  Available
</span>

<!-- Active/Booked -->
<span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
  Active
</span>

<!-- Pending -->
<span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
  Pending
</span>

<!-- Inactive -->
<span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
  Inactive
</span>
```

### Navigation

#### Top Navbar (Black Background)
```yaml
Background: Black-900
Text Color: White
Logo: Left side
Search: Center or left-center
Notifications: Right side (red badge for count)
User Menu: Right side
Height: 16 (64px)
```

#### Sidebar Menu (Black Background)
```yaml
Background: Black-900
Text Color: White
Active Item: Red-600 background
Hover: Black-800
Icons: White, active Red-600
Width: 16 or 20 (256px or 320px)
```

### Feedback Components

#### Toast Notification (Success)
```yaml
Background: White
Border-left: 4px solid Success-Green
Icon: Success-Green
Text: Black-900
```

#### Toast Notification (Error)
```yaml
Background: White
Border-left: 4px solid Red-600
Icon: Red-600
Text: Black-900
```

#### Alert Box (Warning)
```yaml
Background: Warning-Amber-50
Border: Warning-Amber-200
Text: Warning-Amber-900
Icon: Warning-Amber-600
```

### Loading & Progress

#### Loading Spinner
```yaml
Color: Red-600
Size: Various (sm, md, lg)
Style: Rotating circle with red accent
```

#### Progress Bar
```yaml
Background: Gray-200
Fill: Red-600
Height: 2 or 1 (8px or 4px)
Rounded: full
```

---

## 10.4 Page Layouts (Milele Power Style)

### Admin Dashboard Layout
```
┌──────────────────────────────────────────────────────────────┐
│ TOP NAVBAR - Black (#000000)                                 │
│ [Logo: MILELE POWER] [Search] [Notifications] [User Avatar] │
├────────────┬─────────────────────────────────────────────────┤
│            │  PAGE CONTENT - Gray-50 Background              │
│  SIDEBAR   │  ┌───────────────────────────────────────────┐  │
│  Black BG  │  │  KPI Cards - White bg, Red accent top    │  │
│  White text│  │  [Revenue] [Bookings] [Fleet] [Overdue]  │  │
│            │  └───────────────────────────────────────────┘  │
│  Active:   │                                                  │
│  Red-600   │  ┌───────────────────────────────────────────┐  │
│  bg        │  │  Charts Section - White card               │  │
│            │  │  Red line for primary data series         │  │
│  Icons:    │  └───────────────────────────────────────────┘  │
│  White     │                                                  │
│            │  ┌───────────────────────────────────────────┐  │
│  [🏠 Home] │  │  Recent Activity Table                     │  │
│  [📦 Fleet]│  │  Red action buttons                        │  │
│  [📅 Book] │  └───────────────────────────────────────────┘  │
│  [💰 $$]   │                                                  │
│            │  [Primary Action: Red Button]                   │
└────────────┴─────────────────────────────────────────────────┘
```

### List View Layout (e.g., Bookings, Clients)
```
┌──────────────────────────────────────────────────────────────┐
│ Black Navbar                                                  │
├────────────┬─────────────────────────────────────────────────┤
│            │  Breadcrumbs: Home > Bookings                    │
│  Black     │  ┌───────────────────────────────────────────┐  │
│  Sidebar   │  │  PAGE HEADER - White card                  │  │
│            │  │  [H1: Bookings] [+ New Booking - Red Btn] │  │
│            │  └───────────────────────────────────────────┘  │
│            │                                                  │
│            │  ┌───────────────────────────────────────────┐  │
│            │  │  FILTERS & SEARCH - White card             │  │
│            │  │  [Search] [Status▼] [Date Range]          │  │
│            │  └───────────────────────────────────────────┘  │
│            │                                                  │
│            │  ┌───────────────────────────────────────────┐  │
│            │  │  DATA TABLE - White card                   │  │
│            │  │  Header: Black-900, White text             │  │
│            │  │  Rows: Hover gray-50                       │  │
│            │  │  Actions: Red-600 buttons                  │  │
│            │  │  [View] [Edit] [Delete-Red]               │  │
│            │  └───────────────────────────────────────────┘  │
│            │                                                  │
│            │  [Pagination: Red-600 for active page]          │
└────────────┴─────────────────────────────────────────────────┘
```

### Detail/View Page Layout
```
┌──────────────────────────────────────────────────────────────┐
│ Black Navbar                                                  │
├────────────┬─────────────────────────────────────────────────┤
│            │  Breadcrumbs: Home > Bookings > BK-2026-001     │
│  Black     │                                                  │
│  Sidebar   │  ┌───────────────────────────────────────────┐  │
│            │  │  SUMMARY CARD - White, Red accent left     │  │
│            │  │  [Booking: BK-2026-001]                    │  │
│            │  │  Status Badge: Active (Red)                │  │
│            │  │  [Edit - Black] [Cancel - Red] [...]      │  │
│            │  └───────────────────────────────────────────┘  │
│            │                                                  │
│            │  ┌───────────────────────────────────────────┐  │
│            │  │  TABS - Red underline for active           │  │
│            │  │  [Details] [Delivery] [Invoice] [History] │  │
│            │  ├───────────────────────────────────────────┤  │
│            │  │  TAB CONTENT                               │  │
│            │  │  Grid layout with info cards               │  │
│            │  │  Labels: Black, Values: Gray-600           │  │
│            │  └───────────────────────────────────────────┘  │
└────────────┴─────────────────────────────────────────────────┘
```

### Modal Dialog
```
┌──────────────────────────────────────────────────┐
│ Modal Header - White bg, Black text              │
│ ✕ Close button (hover: Red)                     │
├──────────────────────────────────────────────────┤
│ Modal Body                                        │
│ Form fields with Red focus rings                 │
│                                                   │
├──────────────────────────────────────────────────┤
│ Modal Footer - Gray-50 bg                        │
│ [Cancel - Black outline] [Save - Red button]    │
└──────────────────────────────────────────────────┘
```

---

## 10.5 Landing Page Design Specifications

### Public Landing Page (https://www.milelepower.co.tz style)

#### Hero Section
```yaml
Background: Dark gradient (Black-900 to Black-800) with subtle red overlay
Heading: White, 5xl, Bold
  - "Reliable Power, Anytime, Anywhere!"
Subheading: Gray-300, xl
  - "Powering your cold chain logistics — from dock to destination"
CTA Buttons:
  - Primary: "Get Started Now" (Red-600, White text)
  - Secondary: "Request a Quote" (White outline, White text)
Hero Image: Generator/container on right side
Height: 600-700px (full viewport)
```

#### Services Section
```yaml
Background: White or Gray-50
Heading: Black-900, 3xl, "WHY CHOOSING OUR SERVICES"
Grid: 3 columns (desktop), 1-2 columns (mobile)
Service Cards:
  - White background
  - Red icon (48x48px)
  - Black-900 heading
  - Gray-600 description
  - Hover: Slight lift (shadow-lg)
Services:
  1. Quality Services - "EXCEEDING EXPECTATIONS, EVERYTIME 💯"
  2. Reliability & Availability - "Power You Can Trust 🛠️"
  3. Customer Satisfaction - "Your Power, Our Priority 😊👍"
  4. Innovation - "Advancing Solutions 🔬"
  5. Safety & Sustainability - "Protecting Future 🌍"
  6. Commitment To Excellence - "Setting the Standard 🏆"
```

#### Target Clients Section
```yaml
Background: Gray-50
Heading: Black-900, 3xl, "OUR TARGET CLIENTS"
Layout: Stepped cards or grid
Clients:
  1. Meat & Poultry Processing Plants
  2. Dairy Industries
  3. Seafood Processing Companies
  4. Fruit & Vegetable Exporters
  5. Pharmaceutical Cold Storage
  6. Frozen Food Warehouses
Card Style: White, number badge (Red), hover effect
```

#### Stats/Trust Section
```yaml
Background: Black-900
Text: White
Stats Display:
  - "1000+ Successful Rentals" (large number in Red-600)
  - Client testimonials
  - Certifications
Layout: Centered, bold numbers, descriptive text
```

#### Products/Equipment Section
```yaml
Background: White
Heading: "Revolutionize Your Digital Experience Today"
Products:
  - Clip-on Gensets (20ESX)
  - Underslung Gensets
Features:
  ✓ Tailored Rental Packages
  ✓ Eco-Friendly & Cost-Effective Solutions
  ✓ Uninterrupted Power
  ✓ Comprehensive Support
CTA: "Get Started Now" (Red button)
Image: Product/equipment photos
```

#### Contact Section
```yaml
Background: Black-900
Text: White
Columns: 3 (Address | Email | Hours)
Icons: Red-600
Info:
  - Address: Plot No. 80, Mikocheni, Dar Es Salaam
  - Email: info@milelepower.co.tz, accounts@milelepower.co.tz
  - Hours: Mon-Fri 9-5, Sat 9-1
Contact Form: Optional, Red submit button
```

#### Footer
```yaml
Background: Black-900
Text: Gray-400
Links: Gray-400, hover Red-600
Copyright: "© Copyright MILELE POWER LTD All Rights Reserved"
Social Links: Optional
```

---

## 10.6 Icon System

### Icon Library
```yaml
Primary: Heroicons (Outline & Solid) or Boxicons
Style: Consistent stroke width (2px)
Colors:
  - Default: Gray-600
  - Active: Red-600
  - On Dark BG: White
  - On Red Button: White
Sizes:
  - sm: 16x16px
  - md: 20x20px (default)
  - lg: 24x24px
  - xl: 32x32px
```

### Common Icons
```yaml
Navigation:
  - Dashboard: Home icon
  - Fleet: Truck icon
  - Bookings: Calendar icon
  - Clients: Users icon
  - Financial: Currency icon
  - Reports: Chart icon
  - Settings: Cog icon

Actions:
  - Add/Create: Plus icon (Red)
  - Edit: Pencil icon
  - Delete: Trash icon (Red)
  - View: Eye icon
  - Download: Arrow-down icon
  - Upload: Arrow-up icon
  - Search: Magnifying glass

Status:
  - Success: Check-circle (Green)
  - Warning: Exclamation-triangle (Amber)
  - Error: X-circle (Red)
  - Info: Information-circle (Blue)
```

---

## 10.7 Responsive Breakpoints

```yaml
Mobile: 0px - 639px (sm)
Tablet: 640px - 1023px (md - lg)
Desktop: 1024px - 1279px (lg - xl)
Large Desktop: 1280px+ (xl - 2xl)

Tailwind Breakpoints:
  sm: 640px
  md: 768px
  lg: 1024px
  xl: 1280px
  2xl: 1536px

Responsive Behavior:
  - Sidebar: Collapsible on mobile, fixed on desktop
  - Tables: Horizontally scrollable on mobile, responsive cards
  - Forms: Full width on mobile, multi-column on desktop
  - Navigation: Hamburger menu on mobile, full nav on desktop
```

---

## 10.8 Animation & Transitions

```yaml
Transition Duration:
  - Fast: 150ms (hover effects)
  - Normal: 200ms (default)
  - Slow: 300ms (page transitions)

Common Transitions:
  - Button hover: background-color 200ms
  - Card hover: transform + shadow 200ms
  - Modal open: opacity + scale 200ms
  - Sidebar toggle: transform 300ms
  - Page transition: fade 150ms

Easing:
  - Default: ease-in-out
  - Enter: ease-out
  - Exit: ease-in
```

---

# 11. TESTING STRATEGY

## 11.1 Testing Pyramid

```
         ┌──────────────┐
        /  E2E Tests     \      10%
       /    (Slow)        \
      ├────────────────────┤
     /  Integration Tests   \   20%
    /    (Medium Speed)      \
   ├──────────────────────────┤
  /    Unit Tests              \  70%
 /      (Fast)                  \
└──────────────────────────────┘
```

## 11.2 Testing Types

### Unit Tests (Pest/PHPUnit)
```yaml
Coverage Target: 80%+

Test Categories:
  - Model methods
  - Service layer logic
  - Helper functions
  - Calculations
  - Validations
  - Utilities

Example:
  - BookingService::calculateTotalCost()
  - Client::calculateProfileCompletion()
  - CurrencyHelper::formatCurrency()
```

### Feature/Integration Tests
```yaml
Test Scenarios:
  - Complete booking flow
  - Payment processing
  - Invoice generation
  - Approval workflows
  - API endpoints
  - Database transactions

Example:
  test('user can create booking')
  test('system generates invoice after approval')
  test('payment updates invoice status')
```

### Browser Tests (Laravel Dusk)
```yaml
Critical User Flows:
  - User login/logout
  - Create booking (full flow)
  - Approve booking
  - Generate invoice
  - Record payment
  - Schedule delivery
  - Complete delivery
```

### API Tests
```yaml
Tools: Postman / Insomnia
Collections:
  - Authentication endpoints
  - CRUD operations
  - Business logic endpoints
  - Error handling
  - Rate limiting
  - Webhook endpoints
```

### Performance Tests
```yaml
Tools: Apache JMeter / k6
Scenarios:
  - Concurrent users (100, 500, 1000)
  - Database query performance
  - API response times
  - Page load times
  - File upload/download
  
Benchmarks:
  - API response: < 200ms (95th percentile)
  - Page load: < 2s (fully loaded)
  - Database queries: < 50ms (simple), < 200ms (complex)
```

### Security Tests
```yaml
Tests:
  - SQL injection prevention
  - XSS prevention
  - CSRF protection
  - Authentication bypass attempts
  - Authorization checks
  - Input validation
  - File upload restrictions
  - API rate limiting
```

## 11.3 Continuous Testing

### Pre-commit Hooks
```yaml
Checks:
  - PHP syntax errors
  - Code style (Laravel Pint)
  - PHPStan static analysis
  - Unit tests (fast ones)
```

### CI Pipeline Tests
```yaml
On Every Push:
  - All unit tests
  - Code quality checks
  - Security scans
  
On Pull Request:
  - All unit + integration tests
  - Coverage report
  - Performance benchmarks
  
Before Deployment:
  - Full test suite
  - E2E tests
  - Security audit
```

---

# 12. DEPLOYMENT PLAN

## 12.1 Environments

### Development
```yaml
Purpose: Developer workstations
Database: Local MySQL container
Cache: Redis container
Access: Local only (127.0.0.1)
Debug: Enabled
URLs: http://milele.test (Valet/Homestead)
```

### Staging
```yaml
Purpose: Pre-production testing
Server: Separate server / container
Database: Staging database (sanitized production data)
Access: VPN / IP whitelist
Debug: Enabled with logging
URLs: https://staging.milelepower.co.tz
Features:
  - Mimics production
  - Test real integrations
  - User acceptance testing
  - Performance testing
```

### Production
```yaml
Purpose: Live system
Server: Production server(s)
Database: Production database
Access: Public with authentication
Debug: Disabled
URLs: https://app.milelepower.co.tz
Features:
  - High availability
  - Auto-scaling (if needed)
  - Monitoring & alerts
  - Automated backups
```

## 12.2 Deployment Process

### Manual Deployment Checklist
```yaml
Pre-deployment:
  □ Run all tests
  □ Update version number
  □ Update CHANGELOG.md
  □ Database backup
  □ Review pending migrations
  □ Check configuration changes
  
Deployment:
  □ Put application in maintenance mode
  □ Pull latest code (git pull)
  □ Install dependencies (composer install)
  □ Build assets (npm run build)
  □ Run migrations (php artisan migrate)
  □ Clear caches
  □ Restart queue workers
  □ Restart PHP-FPM (if needed)
  □ Take application out of maintenance
  
Post-deployment:
  □ Verify deployment
  □ Check error logs
  □ Test critical features
  □ Monitor performance
  □ Notify team
```

### Automated Deployment (CI/CD)
```yaml
Trigger: Git tag (e.g., v1.2.3)

Pipeline:
  1. Checkout code
  2. Install dependencies
  3. Run tests
  4. Build assets
  5. Create deployment package
  6. Upload to server
  7. Backup database
  8. Run deployment script
  9. Health check
  10. Rollback if failed
  11. Notify on Slack/Email
```

### Zero-Downtime Deployment
```yaml
Strategy: Blue-Green Deployment

Process:
  1. Deploy to "green" environment
  2. Run smoke tests
  3. Switch load balancer to "green"
  4. Monitor for issues
  5. Keep "blue" as fallback
  6. Rollback if needed
```

## 12.3 Rollback Plan

### Automated Rollback
```yaml
Triggers:
  - Failed health checks
  - High error rates
  - Manual intervention

Process:
  1. Switch to previous version
  2. Restore database (if schema changed)
  3. Notify team
  4. Investigate issue
  5. Fix and redeploy
```

### Database Rollback
```yaml
Strategy:
  - Keep previous migration in place
  - Create rollback migration
  - Test rollback before deployment
  - Have backup ready

Process:
  1. Stop application
  2. Restore database backup
  3. Rollback code
  4. Clear caches
  5. Restart application
```

## 12.4 Monitoring & Alerts

### Application Health
```yaml
Checks:
  - HTTP response codes
  - API endpoint availability
  - Database connectivity
  - Queue processing
  - Disk space
  - Memory usage
  - CPU usage
  
Frequency: Every 1-5 minutes
```

### Alerts
```yaml
Critical (Immediate):
  - Application down
  - Database connection failed
  - Payment gateway errors
  - High error rate (> 5%)
  
Warning (15 min delay):
  - Slow response times
  - High memory usage
  - Queue backlog
  - Disk space low
  
Info:
  - Deployment completed
  - Backup completed
  - Daily summary
```

### Logging
```yaml
Levels:
  - Emergency: System unusable
  - Alert: Immediate action required
  - Critical: Critical conditions
  - Error: Runtime errors
  - Warning: Warning messages
  - Notice: Normal but significant
  - Info: Informational messages
  - Debug: Debug messages (dev only)
  
Storage:
  - Daily log files
  - Centralized logging (ELK Stack)
  - 30-day retention
  - Critical logs archived
```

---

# 13. MAINTENANCE & SUPPORT

## 13.1 Support Tiers

### Tier 1: End User Support
```yaml
Handles:
  - Password resets
  - Basic navigation help
  - How-to questions
  - Report issues
  
Response Time:
  - Critical: 1 hour
  - High: 4 hours
  - Medium: 24 hours
  - Low: 48 hours
  
Team: Customer support staff
```

### Tier 2: Technical Support
```yaml
Handles:
  - Bug investigation
  - Configuration issues
  - Integration problems
  - Performance issues
  
Response Time:
  - Critical: 2 hours
  - High: 8 hours
  - Medium: 48 hours
  
Team: Technical support engineers
```

### Tier 3: Development Team
```yaml
Handles:
  - Code-level bugs
  - Architecture issues
  - Complex integrations
  - Security issues
  
Response Time:
  - Critical: 4 hours
  - High: 24 hours
  
Team: Senior developers
```

## 13.2 Maintenance Schedule

### Daily
- Automated backups
- Log review
- Performance monitoring
- Queue health check

### Weekly
- Security updates check
- Dependency updates review
- User feedback review
- Performance report

### Monthly
- Full security audit
- Database optimization
- Cleanup old data
- Review and update documentation
- Team training/updates

### Quarterly
- Major version updates (if any)
- Infrastructure review
- Disaster recovery drill
- User satisfaction survey

## 13.3 Knowledge Base

### Documentation Categories
1. **User Guides**
   - Getting started
   - Feature tutorials
   - Best practices
   - FAQs

2. **Admin Guides**
   - System configuration
   - User management
   - Report generation
   - Troubleshooting

3. **API Documentation**
   - Authentication
   - Endpoints
   - Request/response examples
   - Rate limits

4. **Developer Docs**
   - Setup guide
   - Architecture overview
   - Code standards
   - Contribution guidelines

---

# APPENDICES

## Appendix A: Glossary of Terms

**Genset**: Portmanteau of "generator set" - An engine-generator combination  
**Cold Chain**: Temperature-controlled supply chain  
**POD**: Proof of Delivery  
**TRA**: Tanzania Revenue Authority  
**EFD**: Electronic Fiscal Device  
**VFD**: Virtual Fiscal Device  
**VRN**: VAT Registration Number  
**TIN**: Tax Identification Number  
**VAT**: Value Added Tax  
**WHT**: Withholding Tax  
**MSA**: Master Service Agreement  
**SLA**: Service Level Agreement  
**RFM**: Recency, Frequency, Monetary (customer segmentation)  
**DSO**: Days Sales Outstanding  
**MTBF**: Mean Time Between Failures  
**MTTR**: Mean Time To Repair  
**IoT**: Internet of Things  

## Appendix B: Contact Information

**Project Owner**: [Your Name/Company]  
**Technical Lead**: [Name]  
**Project Manager**: [Name]  
**Support Email**: support@milelepower.co.tz  
**Emergency Hotline**: [Phone Number]  

## Appendix C: References

- Laravel Documentation: https://laravel.com/docs  
- Tailwind CSS Documentation: https://tailwindcss.com/docs  
- Alpine.js Documentation: https://alpinejs.dev  
- Milele Power Website: https://www.milelepower.co.tz  
- Tanzania Revenue Authority: https://www.tra.go.tz  
- PHP Standards: https://www.php-fig.org/psr/  
- RESTful API Design: https://restfulapi.net/  

---

# IMMEDIATE ACTION PLAN (MARCH 18, 2026)

## Current Status Summary

✅ **COMPLETED TODAY**:
- Reviewed complete system roadmap
- Analyzed Milele Power website (www.milelepower.co.tz)
- Identified official brand colors: Black (#000000) & Red (#DC2626)
- Documented complete design system specifications
- Updated roadmap with branding requirements

🔄 **IN PROGRESS**:
- Nothing currently in development

⏭️ **NEXT PRIORITIES**:
- Begin Phase 0: Design Foundation (2 weeks)

---

## Week 1-2: Design Foundation Sprint (IMMEDIATE START)

### Day 1-2: Tailwind Configuration & Setup
```bash
Priority: CRITICAL
Task List:
  1. Configure tailwind.config.js with Milele Power colors
  2. Set up custom color palette (blacks, reds, grays)
  3. Configure typography (Inter font)
  4. Set up spacing and sizing customizations
  5. Configure custom border radius and shadows
  6. Test build process (npm run dev)
```

### Day 3-5: Core Component Library
```bash
Priority: CRITICAL
Task List:
  1. Build button components (red primary, black secondary)
  2. Create form input components (red focus rings)
  3. Build card components (white with red accents)
  4. Create table components (black headers)
  5. Build status badge components
  6. Create modal/dialog components
  7. Build navigation components (sidebar, navbar)
```

### Day 6-8: Layout Templates
```bash
Priority: HIGH
Task List:
  1. Create auth layout (login/register pages)
  2. Build admin layout (black sidebar + navbar)
  3. Create public landing page layout
  4. Build dashboard shell
  5. Test responsive behavior (mobile, tablet, desktop)
```

### Day 9-10: Authentication Pages
```bash
Priority: HIGH
Task List:
  1. Design login page (black & red theme)
  2. Build registration page
  3. Create password reset flow
  4. Style validation messages (red for errors)
  5. Add Milele Power branding (logo, colors)
```

### Day 11-14: Landing Page & Documentation
```bash
Priority: MEDIUM
Task List:
  1. Build public landing page (hero section)
  2. Create services section (6 cards)
  3. Add target clients section
  4. Build contact section (black background)
  5. Create footer (black with company info)
  6. Write style guide documentation
  7. Create component usage examples
  8. Test all pages for accessibility
```

---

## Developer Checklist (START NOW)

### Environment Setup
- [ ] Pull latest code from repository
- [ ] Install/update Node.js dependencies: `npm install`
- [ ] Install/update Composer dependencies: `composer install`
- [ ] Configure local environment (.env file)
- [ ] Run database migrations: `php artisan migrate`
- [ ] Start local server: `php artisan serve`
- [ ] Start Vite dev server: `npm run dev`

### Tailwind Configuration
- [ ] Create/update `tailwind.config.js` with Milele Power colors
- [ ] Update `vite.config.js` if needed
- [ ] Test Tailwind build: `npm run build`
- [ ] Verify colors are available in CSS

### Git Workflow
- [ ] Create new branch: `git checkout -b feature/design-system`
- [ ] Commit incrementally with clear messages
- [ ] Push regularly to backup work
- [ ] Create PR when design system complete

### Testing Requirements
- [ ] Test on Chrome, Firefox, Safari
- [ ] Test on mobile devices (iOS, Android)
- [ ] Verify accessibility (keyboard navigation, screen readers)
- [ ] Check page load times (<2s)
- [ ] Validate responsive breakpoints

---

## Success Milestones

### Milestone 1: Tailwind Configured (Day 2)
✓ Tailwind config file created with Milele Power colors  
✓ Build process working  
✓ Colors accessible in views  

### Milestone 2: Component Library (Day 5)
✓ All core components built  
✓ Components are reusable  
✓ Mobile responsive  
✓ Accessible  

### Milestone 3: Layouts Complete (Day 8)
✓ Auth layout functional  
✓ Admin layout (sidebar + navbar) working  
✓ Dashboard shell ready  

### Milestone 4: Authentication Pages (Day 10)
✓ Login page styled  
✓ Registration page styled  
✓ Password reset styled  
✓ All forms working  

### Milestone 5: Landing Page (Day 14)
✓ Public landing page complete  
✓ Matches website style  
✓ All sections present  
✓ Mobile responsive  
✓ Style guide documented  

---

## Post-Design System (Week 3+)

Once design foundation is complete:

1. **Sprint 1-2**: System Upgrade & Stability  
   - Migrate existing views to new design
   - Apply Milele Power branding throughout
   - Laravel 12 upgrade

2. **Sprint 3-4**: Fuel Management System
   - Build with new design system
   - Charts with black/red theme

3. **Sprint 5-6**: TRA Compliance
   - Professional invoice templates
   - Branded PDF documents

4. Continue with Phase 1 roadmap...

---

**END OF MASTER ROADMAP DOCUMENT**

**Document Version**: 3.0  
**Last Updated**: March 18, 2026  
**Status**: Ready for Implementation - Design Phase  
**Website**: https://www.milelepower.co.tz  

**Immediate Next Steps**:
1. ✅ Roadmap updated with branding specifications
2. ⏭️ Begin Phase 0: Design Foundation (Week 1-2)
3. ⏭️ Configure Tailwind with Milele Power colors
4. ⏭️ Build component library (black & red theme)
5. ⏭️ Create authentication pages with branding

**Critical Decision Points**:
- Tailwind configuration must be completed before any feature work
- All new components must use official color palette
- Design system is BLOCKING requirement for all future sprints

---

*This document is a living document and will be updated as the project evolves. All team members should refer to this as the single source of truth for project direction, technical specifications, and design standards.*
