// =====================================================
// UNIVERSAL LEAD CRM WEBHOOK (Reusable for any bot)
// Compatible with Flowise Tool Script
// UPDATED to match actual sheet columns (32 columns)
// =====================================================

const SHEET_NAME = "Leads";

// =====================================================
// MAIN POST HANDLER
// =====================================================

function doPost(e) {
  try {
    if (!e || !e.postData || !e.postData.contents) {
      throw new Error("Invalid POST data");
    }

    const data = JSON.parse(e.postData.contents);

    const spreadsheet = SpreadsheetApp.getActiveSpreadsheet();
    let sheet = spreadsheet.getSheetByName(SHEET_NAME);

    // =================================================
    // 📋 ACTUAL SHEET HEADERS (32 columns)
    // =================================================

    const headers = [
      "Lead_ID",
      "Lead_Name",
      "Email", 
      "Phone",
      "Phone_Secondary",
      "Company_Name",
      "Job_Title",
      "Industry",
      "Lead_Source",
      "Lead_Status",
      "Lead_Score",
      "Estimated_Value",
      "Currency",
      "Products_Interested",
      "Country",
      "City",
      "Address",
      "Assigned_To",
      "Date_Created",
      "Created_By",
      "Last_Updated",
      "Updated_By",
      "Next_Follow_Up_Date",
      "Last_Contact_Date",
      "Priority",
      "Notes",
      "Tags",
      "Conversion_Probability",
      "Days_In_Pipeline",
      "Website",
      "LinkedIn_URL",
      "Customer_Name"
    ];

    // =================================================
    // 🏗️ CREATE SHEET IF NOT EXISTS
    // =================================================

    if (!sheet) {
      sheet = spreadsheet.insertSheet(SHEET_NAME);
      sheet.appendRow(headers);
    }

    const phone = data.Phone || "n/a";

    if (phone === "n/a") {
      throw new Error("Phone is required for lead tracking");
    }

    const lastRow = sheet.getLastRow();

    // =================================================
    // 🔍 CHECK IF PHONE ALREADY EXISTS
    // =================================================

    let existingRow = null;

    if (lastRow > 1) {
      const phoneRange = sheet
        .getRange(2, 4, lastRow - 1, 1)  // Column 4 = Phone
        .getValues();

      for (let i = 0; i < phoneRange.length; i++) {
        if (String(phoneRange[i][0]) === String(phone)) {
          existingRow = i + 2;
          break;
        }
      }
    }

    // =================================================
    // 📦 BUILD ROW DATA (MATCHES ACTUAL HEADERS)
    // =================================================

    const now = new Date();
    const leadId = "LD" + now.getTime(); // Simple Lead ID generator

    const rowData = [
      existingRow ? sheet.getRange(existingRow, 1).getValue() : leadId,  // Lead_ID
      data.Lead_Name || "n/a",                                          // Lead_Name
      data.Email || "n/a",                                              // Email
      phone,                                                             // Phone
      data.Phone_Secondary || "n/a",                                     // Phone_Secondary
      data.Company_Name || "n/a",                                        // Company_Name
      data.Job_Title || "n/a",                                           // Job_Title
      data.Industry || "n/a",                                            // Industry
      data.Lead_Source || "Flowise Bot",                                 // Lead_Source
      data.Lead_Status || "Open",                                         // Lead_Status
      data.Lead_Score || 0,                                              // Lead_Score
      data.Estimated_Value || "n/a",                                     // Estimated_Value
      data.Currency || "INR",                                            // Currency
      data.Products_Interested || "n/a",                                 // Products_Interested
      data.Country || "India",                                           // Country
      data.City || "n/a",                                                // City
      data.Address || "n/a",                                             // Address
      data.Assigned_To || "n/a",                                         // Assigned_To
      existingRow ? sheet.getRange(existingRow, 19).getValue() : now,    // Date_Created (preserve if exists)
      data.Created_By || "Bot",                                          // Created_By
      now,                                                                // Last_Updated
      data.Updated_By || "Bot",                                          // Updated_By
      data.Next_Follow_Up_Date || "",                                     // Next_Follow_Up_Date
      data.Last_Contact_Date || now,                                      // Last_Contact_Date
      data.Priority || "Medium",                                         // Priority
      data.Notes || "n/a",                                               // Notes
      data.Tags || "",                                                    // Tags
      data.Conversion_Probability || 0,                                  // Conversion_Probability
      data.Days_In_Pipeline || 0,                                         // Days_In_Pipeline
      data.Website || "n/a",                                             // Website
      data.LinkedIn_URL || "n/a",                                        // LinkedIn_URL
      data.Customer_Name || data.Lead_Name || "n/a"                       // Customer_Name
    ];

    // =================================================
    // 🔄 UPDATE EXISTING LEAD (UPSERT)
    // =================================================

    if (existingRow) {
      const existingData = sheet
        .getRange(existingRow, 1, 1, headers.length)
        .getValues()[0];

      const updatedRow = rowData.map((value, index) => {
        // Do NOT overwrite with "n/a" or empty values
        if ((value !== "n/a" && value !== "" && value !== 0) || index === 20) { // Always update Last_Updated
          return value;
        }
        return existingData[index];
      });

      sheet
        .getRange(existingRow, 1, 1, headers.length)
        .setValues([updatedRow]);

      return successResponse("Lead updated");
    }

    // =================================================
    // ➕ INSERT NEW LEAD
    // =================================================

    sheet.appendRow(rowData);

    return successResponse("Lead inserted");

  } catch (err) {
    Logger.log("Error: " + err.message);

    return ContentService
      .createTextOutput(JSON.stringify({
        success: false,
        error: err.message
      }))
      .setMimeType(ContentService.MimeType.JSON);
  }
}

// =====================================================
// SUCCESS RESPONSE
// =====================================================

function successResponse(message) {
  return ContentService
    .createTextOutput(JSON.stringify({
      success: true,
      message: message
    }))
    .setMimeType(ContentService.MimeType.JSON);
}
