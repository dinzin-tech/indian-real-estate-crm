/**
 * GoogleSheetsLeadSyncTool
 * Compatible with Universal Lead Capture System Prompt
 * Flowise Tool Script — MUST return STRING
 * UPDATED to match actual Google Sheet columns
 */

const axios = require("axios");

// ===================================
// 🔧 CONFIGURATION
// ===================================

// Replace with your Apps Script Web App URL
const WEBHOOK_URL = "https://script.google.com/macros/s/XXXXXXXXXXXX/exec";

// Identify lead source (optional)
const SOURCE_LABEL = "Flowise Lead Assistant";

// ===================================
// 🧠 SESSION INFO (Flowise context)
// ===================================

const sessionId = $flow?.sessionId || "n/a";
const chatId = $flow?.chatId || "n/a";

// ===================================
// 📥 SAFE VARIABLE HELPER
// ===================================

function getVar(v) {
  return (typeof v === "undefined" || v === null || v === "")
    ? "n/a"
    : v;
}

// ===================================
// 📦 PAYLOAD — MATCHES ACTUAL SHEET COLUMNS
// ===================================

const payload = {
  // Core Lead Info (matches sheet columns)
  Lead_Name: getVar($name),
  Phone: getVar($phone),
  Email: getVar($email),
  City: getVar($city),
  Country: getVar($country) || "India", // Default to India
  
  // Qualification Data (mapped to sheet columns)
  Products_Interested: getVar($requirement_type),
  Estimated_Value: getVar($budget),
  
  // Additional Info
  Notes: getVar($additional_notes) + (getVar($timeline) !== "n/a" ? " | Timeline: " + getVar($timeline) : ""),
  
  // System Metadata
  Lead_Source: SOURCE_LABEL,
  Date_Created: new Date().toISOString(),
  Lead_Status: getVar($lead_status) || "Open",
  Priority: getVar($priority) || "Medium",
  
  // Flowise specific
  sessionId: sessionId,
  chatId: chatId
};

// ===================================
// 🚀 SEND TO GOOGLE SHEET / CRM
// ===================================

await axios.post(WEBHOOK_URL, payload, {
  headers: { "Content-Type": "application/json" },
  timeout: 15000,
  validateStatus: status => status >= 200 && status < 500
});

// ===================================
// ✅ REQUIRED RETURN VALUE
// ===================================

return "OK";
