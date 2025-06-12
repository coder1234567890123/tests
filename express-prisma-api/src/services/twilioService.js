// In express-prisma-api/src/services/twilioService.js
import twilio from 'twilio';
import dotenv from 'dotenv';

dotenv.config({ path: '../.env' }); // Assuming .env is at express-prisma-api root

const accountSid = process.env.TWILIO_ACCOUNT_SID;
const authToken = process.env.TWILIO_AUTH_TOKEN;
const twilioPhoneNumber = process.env.TWILIO_PHONE_NUMBER;

let client;
// Check if actual credentials are provided, not just placeholders
if (accountSid && authToken && !accountSid.startsWith('ACxxxx') && !authToken.startsWith('your_auth_token')) {
  client = twilio(accountSid, authToken);
} else {
  console.warn("Twilio client not initialized. Missing or placeholder Account SID/Auth Token in .env. SMS functionality will be disabled.");
}

export const sendSms = async (toPhoneNumber, messageBody) => {
  if (!client) {
    console.warn(`Twilio client not initialized. SMS not sent to ${toPhoneNumber}. Message: "${messageBody}"`);
    return Promise.resolve({ success: false, error: 'Twilio client not initialized.' });
  }
  if (!toPhoneNumber || !twilioPhoneNumber) {
     console.warn('Recipient phone number or Twilio phone number is missing. SMS not sent.');
     return Promise.resolve({ success: false, error: 'Missing phone number(s).' });
  }

  // Basic E.164 format check (starts with +, then digits) - very basic
  if (!/^\+[1-9]\d{1,14}$/.test(toPhoneNumber)) {
    console.warn(`Invalid "To" phone number format: ${toPhoneNumber}. Must be E.164. SMS not sent.`);
    return Promise.resolve({ success: false, error: `Invalid "To" phone number format: ${toPhoneNumber}.` });
  }
  if (!/^\+[1-9]\d{1,14}$/.test(twilioPhoneNumber)) {
    console.warn(`Invalid "From" (Twilio) phone number format: ${twilioPhoneNumber}. Must be E.164. SMS not sent.`);
    return Promise.resolve({ success: false, error: `Invalid "From" phone number format: ${twilioPhoneNumber}.` });
  }


  try {
    const message = await client.messages.create({
      body: messageBody,
      from: twilioPhoneNumber,
      to: toPhoneNumber,
    });
    console.log(`SMS sent to ${toPhoneNumber} with SID: ${message.sid}`);
    return { success: true, sid: message.sid };
  } catch (error) {
    console.error(`Error sending SMS via Twilio to ${toPhoneNumber}:`, error.message);
    // Log more details if available, but don't expose too much in API response
    let errorMessage = error.message;
    if (error.code === 21211) { // Example: Invalid 'To' Phone Number
        errorMessage = `The recipient phone number (${toPhoneNumber}) is not a valid phone number.`;
    } else if (error.code === 21608) { // Example: The 'From' phone number is not a valid, SMS-capable Twilio phone number
        errorMessage = `The Twilio phone number (${twilioPhoneNumber}) is not valid or SMS-capable.`;
    }
    return { success: false, error: errorMessage, code: error.code };
  }
};
