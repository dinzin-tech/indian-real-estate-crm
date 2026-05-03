/*********************************************************
 REAL ESTATE FOLLOW-UP AUTOMATION (Reusable)
 Sends WhatsApp templates via WhatyPie API
 Set as Time Trigger: run daily (ex: every morning 10am)
 UPDATED to match actual sheet columns
**********************************************************/

const SHEET_NAME = "Leads";

// ===============================
// WHATYPIE CONFIG
// ===============================

const WHATYPIE_TOKEN = "YOUR_BEARER_TOKEN";

// Replace with actual WhatyPie template send endpoint
// Confirm exact endpoint from your account docs/dashboard.
const WHATYPIE_ENDPOINT = "https://whatypie.com/api/v1/messages/template";

// Optional if using dedicated sender number id
const FROM_PHONE_NUMBER_ID = "";

// ===============================
// FOLLOW-UP SEQUENCE
// days_after_lead_creation
// ===============================

const FOLLOWUPS = [
  {
    stage: 1,
    dayOffset: 1,
    template_name: "realestate_followup_day1",
    language: "en"
  },
  {
    stage: 2,
    dayOffset: 3,
    template_name: "realestate_followup_day3",
    language: "en"
  },
  {
    stage: 3,
    dayOffset: 7,
    template_name: "realestate_followup_day7",
    language: "en"
  },
  {
    stage: 4,
    dayOffset: 14,
    template_name: "realestate_followup_day14",
    language: "en"
  }
];


// ==================================================
// MAIN JOB
// ==================================================

function runFollowupAutomation() {

  const sheet = SpreadsheetApp
      .getActiveSpreadsheet()
      .getSheetByName(SHEET_NAME);

  if (!sheet) throw new Error("Sheet not found");

  const data = sheet.getDataRange().getValues();

  if (data.length <= 1) return;

  const headers = data[0];

  const col = indexMap(headers);

  for (let r = 1; r < data.length; r++) {

    const row = data[r];

    const leadStatus = value(row,col,"Lead_Status","Open");
    const phone = normalizePhone(value(row,col,"Phone",""));
    const name = value(row,col,"Lead_Name","there");
    const priority = value(row,col,"Priority","Medium");

    if (!phone) continue;

    // STOP conditions
    if (
      leadStatus === "Closed" ||
      leadStatus === "Replied" ||
      leadStatus === "Do Not Contact" ||
      leadStatus === "Converted" ||
      leadStatus === "Lost"
    ) {
      continue;
    }

    const createdDate = parseDate(
      value(row,col,"Date_Created", new Date())
    );

    const daysOld = diffDays(createdDate, new Date());

    // Get last contact date to calculate follow-up stage
    const lastContactDate = parseDate(
      value(row,col,"Last_Contact_Date", createdDate)
    );
    
    const daysSinceLastContact = diffDays(lastContactDate, new Date());
    
    // Determine current stage based on days since creation
    const currentStage = getStageFromDays(daysOld);

    const nextFollowup = getNextFollowup(currentStage, daysOld);

    if (!nextFollowup) continue;

    const sent = sendTemplateMessage(
      phone,
      nextFollowup.template_name,
      nextFollowup.language,
      {
        field_1: name
      }
    );

    if (sent.success) {

      // Update Last_Contact_Date (column 24)
      setCell(
        sheet,
        r+1,
        col["Last_Contact_Date"],
        new Date()
      );
      
      // Update Next_Follow_Up_Date (column 23) - set to 3 days from now
      setCell(
        sheet,
        r+1,
        col["Next_Follow_Up_Date"],
        new Date(Date.now() + 3 * 24 * 60 * 60 * 1000)
      );

      Utilities.sleep(1200); // rate limit buffer
    }
  }
}



// ==================================================
// DETERMINE CURRENT STAGE FROM DAYS
// ==================================================

function getStageFromDays(daysOld) {
  if (daysOld < 3) return 0;       // Before day 3
  if (daysOld < 7) return 1;       // Day 3-6
  if (daysOld < 14) return 2;      // Day 7-13
  if (daysOld >= 14) return 3;     // Day 14+
  return 0;
}

// ==================================================
// DETERMINE NEXT FOLLOWUP
// ==================================================

function getNextFollowup(currentStage, daysOld) {

  for (let i = 0; i < FOLLOWUPS.length; i++) {

    const f = FOLLOWUPS[i];

    if (
      f.stage === currentStage + 1 &&
      daysOld >= f.dayOffset
    ) {
      return f;
    }
  }

  return null;
}



// ==================================================
// SEND TEMPLATE VIA WHATYPIE
// ==================================================

function sendTemplateMessage(phone, templateName, lang, vars) {

  try {

    const payload = {
      from_phone_number_id: FROM_PHONE_NUMBER_ID,
      phone_number: phone,
      template_name: templateName,
      template_language: lang,
      field_1: vars.field_1 || ""
    };

    const options = {
      method: "post",
      contentType: "application/json",
      headers: {
        Authorization: "Bearer " + WHATYPIE_TOKEN
      },
      payload: JSON.stringify(payload),
      muteHttpExceptions: true
    };

    const response = UrlFetchApp.fetch(
      WHATYPIE_ENDPOINT,
      options
    );

    const code = response.getResponseCode();

    Logger.log(response.getContentText());

    return {
      success: code >= 200 && code < 300
    };

  } catch(e) {

    Logger.log(e);

    return {
      success:false
    };
  }
}



// ==================================================
// HELPERS
// ==================================================

function normalizePhone(phone) {
  return String(phone).replace(/[^\d]/g,'');
}

function diffDays(d1,d2) {
  return Math.floor(
    (d2-d1)/(1000*60*60*24)
  );
}

function parseDate(v) {
  return v instanceof Date
    ? v
    : new Date(v);
}

function value(row,map,key,def) {
  const idx = map[key];
  if(idx === undefined) return def;

  return row[idx-1] || def;
}

function setCell(sheet,row,col,val) {
  if(!col) return;
  sheet.getRange(row,col).setValue(val);
}

function indexMap(headers) {

  const map = {};

  headers.forEach(function(h,i) {
    map[String(h).trim()] = i+1;
  });

  return map;
}
