import puppeteer from 'puppeteer';
import prisma from '../utils/prismaClient.js';

const formatDate = (dateInput) => {
  if (!dateInput) return 'N/A';
  const date = typeof dateInput === 'string' ? new Date(dateInput) : dateInput;
  if (isNaN(date.getTime())) return 'Invalid Date';
  return date.toLocaleDateString();
};

const sanitizeHTML = (text) => {
  if (text === null || text === undefined) return '';
  const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
  return String(text).replace(/[&<>"']/g, (m) => map[m]);
};

const getReportHTML = (report, subject, reportCompany, defaultBrandingSettings) => {
  const appBaseUrl = process.env.BACKEND_URL || 'http://localhost:3001';
  const appNameFallback = process.env.APP_NAME || "Your Application";


  // Initialize with defaults, then override based on branding
  let themeColor = defaultBrandingSettings?.themeColor || '#003366';
  let themeColorSecond = defaultBrandingSettings?.themeColorSecond || '#555555';
  let coverLogoUrl = defaultBrandingSettings?.coverLogo ? `${appBaseUrl}/uploads/system-assets/${defaultBrandingSettings.coverLogo}` : '';
  let mainLogoUrl = defaultBrandingSettings?.logo ? `${appBaseUrl}/uploads/system-assets/${defaultBrandingSettings.logo}` : '';
  // footerLogoUrl will be determined later for use in puppeteer options, not directly in HTML here for the template.
  let frontPageImageUrl = defaultBrandingSettings?.frontPage ? `${appBaseUrl}/uploads/system-assets/${defaultBrandingSettings.frontPage}` : '';
  let coBrandedCompanyLogoUrl = ''; // Specific for co-branded type. Default can be from defaultBrandingSettings.coFrontPage
  if (defaultBrandingSettings?.coFrontPage) { // If there's a default co-brand logo (system asset)
    coBrandedCompanyLogoUrl = `${appBaseUrl}/uploads/system-assets/${defaultBrandingSettings.coFrontPage}`;
  }

  let finalDisclaimer = defaultBrandingSettings?.disclaimer || '';
  let finalFooterLink = defaultBrandingSettings?.footerLink || '';
  let finalCompanyNameForFooter = defaultBrandingSettings?.appName || appNameFallback;


  if (reportCompany) {
    finalCompanyNameForFooter = reportCompany.name || finalCompanyNameForFooter;
    const brandingType = reportCompany.brandingType;

    if (brandingType === 'white_label') {
      themeColor = reportCompany.themeColor || themeColor;
      themeColorSecond = reportCompany.themeColorSecond || themeColorSecond;
      coverLogoUrl = reportCompany.coverLogo ? `${appBaseUrl}/uploads/company-images/${reportCompany.id}/${reportCompany.coverLogo}` : '';
      mainLogoUrl = coverLogoUrl;
      // footerLogoUrl determined in generateReportPDF for template
      frontPageImageUrl = reportCompany.imageFrontPage ? `${appBaseUrl}/uploads/company-images/${reportCompany.id}/${reportCompany.imageFrontPage}` : '';
      coBrandedCompanyLogoUrl = '';
      if (reportCompany.useDisclaimer && reportCompany.disclaimer) finalDisclaimer = reportCompany.disclaimer;
      if (reportCompany.footerLink) finalFooterLink = reportCompany.footerLink;
    } else if (brandingType === 'co_branded') {
      themeColor = reportCompany.themeColor || themeColor;
      themeColorSecond = reportCompany.themeColorSecond || themeColorSecond;
      // Company's logo is used as the co-branded logo, default main/cover logos are primary
      if (reportCompany.coverLogo) { // This company's logo for co-branding
        coBrandedCompanyLogoUrl = `${appBaseUrl}/uploads/company-images/${reportCompany.id}/${reportCompany.coverLogo}`;
      }
      // footerLogoUrl determined in generateReportPDF for template
      if (reportCompany.useDisclaimer && reportCompany.disclaimer) finalDisclaimer = reportCompany.disclaimer;
      if (reportCompany.footerLink) finalFooterLink = reportCompany.footerLink;
    }
  }

  const styles = `
    body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 0; color: #333333; font-size: 10pt; line-height: 1.5; -webkit-font-smoothing: antialiased; }
    .page { padding: 0; page-break-after: always; position: relative; min-height: calc(297mm - 50mm); /* A4 height minus top/bottom margins for @page */ box-sizing: border-box; }
    .cover-page { background-color: ${sanitizeHTML(themeColor)}; color: white; height: 297mm; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; box-sizing: border-box; background-size: cover; background-position: center; padding: 20mm;}
    .cover-page img.logo { max-height: 80px; margin-bottom: 20px; }
    .cover-page img.co-logo { max-height: 50px; margin-top: 15px; }
    .cover-page h1 { font-size: 28pt; margin-bottom: 15px; color: white; font-weight: 300;}
    .cover-page h2 { font-size: 20pt; margin-bottom: 25px; color: #e0e0e0; font-weight: 300;}
    .cover-page p { font-size: 11pt; color: #cccccc;}
    .report-header { text-align: center; margin-bottom: 25px; padding-bottom:15px; border-bottom: 2px solid ${sanitizeHTML(themeColor)};}
    .report-header img.main-logo { max-height: 60px; margin-bottom: 10px; }
    .report-header h1 { font-size: 22pt; color: ${sanitizeHTML(themeColor)}; margin-bottom: 5px; font-weight: bold;}
    .report-header p { font-size: 10pt; color: #555; margin: 2px 0; }
    .section { margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid #eeeeee; overflow-wrap: break-word; word-wrap: break-word; page-break-inside: avoid; }
    .section:last-of-type { border-bottom: none; }
    .section h2 { font-size: 16pt; color: ${sanitizeHTML(themeColor)}; margin-bottom: 12px; border-bottom: 1px solid #e0e0e0; padding-bottom: 6px; font-weight: normal;}
    .section h3 { font-size: 13pt; color: #222222; margin-top: 12px; margin-bottom: 6px; font-weight: bold; }
    .section p, .section li { margin-bottom: 6px; }
    .section ul { list-style: disc; padding-left: 18px; }
    .grid { display: grid; grid-template-columns: 150px 1fr; gap: 6px; margin-bottom: 7px; }
    .grid strong { font-weight: bold; color: #444444; }
    .platform-card { border: 1px solid #e0e0e0; border-left: 4px solid ${sanitizeHTML(themeColorSecond)}; margin-bottom: 15px; padding: 12px; border-radius: 3px; background-color: #f9f9f9; page-break-inside: avoid;}
    .question-block { margin-left: 10px; margin-bottom: 12px; padding-left:10px; border-left: 2px solid #dddddd;}
    .question-block p strong { color: #333; }
    .answer-block { background-color: #f0f8ff; padding: 8px; border-radius: 3px; margin-left: 0px; margin-top: 5px; margin-bottom: 5px; border: 1px solid #d1e7fd;}
    .proof-block { background-color: #f5f5f5; padding: 8px; border-radius: 3px; margin-left: 10px; margin-top: 8px; border: 1px solid #e7e7e7;}
    .proof-block strong { font-weight: bold; color: #333; }
    .proof-file-link { font-size: 9pt; color: #0066cc; display: block; margin-top: 3px; }
    .behaviour-scores-list { font-size: 9pt; color: #555555; margin-top:3px; padding-left: 15px; list-style-type: none;}
    .comment-block { border-top: 1px dashed #cccccc; padding-top: 12px; margin-top:18px; }
    .comment-item { background-color: #fafafa; border: 1px solid #eee; padding: 8px; border-radius: 3px; margin-bottom:8px; }
    .comment-meta { font-size: 9pt; color: #777777; }
    .score-summary { background-color: #f0f4f8; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #d4e0eb;}
    .score-summary p { margin: 4px 0; }
    .risk-score { font-size: 22pt; font-weight: bold; color: ${report.riskScore != null && report.riskScore > 50 ? '#c0392b' : (report.riskScore != null && report.riskScore > 30 ? '#f39c12' : '#27ae60')}; }
    .empty-section { font-style: italic; color: #777; font-size: 9pt; }
    .disclaimer { font-size: 8pt; color: #666666; margin-top: 25px; }
  `;

  let html = `<html><head><meta charset="utf-8"><title>Report ${sanitizeHTML(report.sequence)}</title><style>${styles}</style></head><body>`;

  html += `<div class="page cover-page" style="${frontPageImageUrl ? `background-image: url('${sanitizeHTML(frontPageImageUrl)}');` : ''}">`;
  if (coverLogoUrl) html += `<img src="${sanitizeHTML(coverLogoUrl)}" alt="Logo" class="logo">`;
  if (coBrandedCompanyLogoUrl) html += `<img src="${sanitizeHTML(coBrandedCompanyLogoUrl)}" alt="Co-Brand Logo" class="co-logo">`;
  html += `<h1>Social Media Screening Report</h1><h2>${sanitizeHTML(subject.firstName || '')} ${sanitizeHTML(subject.lastName || '')}</h2>`;
  html += `<p>Report ID: ${sanitizeHTML(report.sequence)}</p><p>Date Generated: ${formatDate(new Date().toISOString())}</p>`;
  if (reportCompany) html += `<p>Prepared for: ${sanitizeHTML(reportCompany.name)}</p>`;
  else if (defaultBrandingSettings?.appName) html += `<p>Prepared by: ${sanitizeHTML(defaultBrandingSettings.appName)}</p>`;
  html += `</div>`;

  html += `<div class="page page-content"><div class="report-header">`;
  if (mainLogoUrl) html += `<img src="${sanitizeHTML(mainLogoUrl)}" alt="Main Logo" class="main-logo">`;
  else if (coBrandedCompanyLogoUrl && brandingType === 'co_branded') html += `<img src="${sanitizeHTML(coBrandedCompanyLogoUrl)}" alt="Company Logo" class="main-logo">`;
  html += `<h1>Social Media Screening Report</h1><p><strong>Subject:</strong> ${sanitizeHTML(subject.firstName || '')} ${sanitizeHTML(subject.middleName) || ''} ${sanitizeHTML(subject.lastName) || ''} (${sanitizeHTML(subject.identification)})</p>`;
  html += `<p><strong>Report ID:</strong> ${sanitizeHTML(report.sequence)} | <strong>Date Requested:</strong> ${formatDate(report.createdAt)}</p>`;
  if (report.requestType) html += `<p><strong>Report Type:</strong> ${sanitizeHTML(report.requestType.replace(/_/g, ' '))}</p>`;
  html += `</div>`;

  html += `<div class="section score-summary"><h2>Overall Assessment</h2>`;
  html += `<p><strong>Overall Risk Score:</strong> <span class="risk-score">${report.riskScore !== null && report.riskScore !== undefined ? report.riskScore.toFixed(2) + '%' : 'N/A'}</span></p>`;
  if (report.reportScores?.weighted_social_media_score_round !== undefined) html += `<p>Weighted Social Media Score: ${report.reportScores.weighted_social_media_score_round.toFixed(2)}</p>`;
  if (report.riskComment && report.riskComment !== "none") html += `<p><strong>Analyst Risk Comment:</strong> ${sanitizeHTML(report.riskComment)}</p>`;
  if (report.reportScores?.overall_behavior_scores && Object.keys(report.reportScores.overall_behavior_scores).length > 0) {
    html += `<h3>Overall Behavioural Scores:</h3><ul>`;
    Object.entries(report.reportScores.overall_behavior_scores).forEach(([key, value]) => {
      html += `<li><strong>${sanitizeHTML(key.replace(/_/g, ' '))}</strong>: ${sanitizeHTML(String(value))}</li>`;
    });
    html += `</ul>`;
  } else { html += `<p class="empty-section">No overall behavioural scores available.</p>`;}
  html += `</div>`;

  html += `<div class="section"><h2>Subject Information</h2>`;
  html += `<div class="grid"><p><strong>Full Name:</strong></p><p>${sanitizeHTML(subject.firstName) || ''} ${sanitizeHTML(subject.middleName) || ''} ${sanitizeHTML(subject.lastName) || ''}</p></div>`;
  html += `<div class="grid"><p><strong>Identification:</strong></p><p>${sanitizeHTML(subject.identification)}</p></div>`;
  if (subject.dateOfBirth) html += `<div class="grid"><p><strong>Date of Birth:</strong></p><p>${formatDate(subject.dateOfBirth)}</p></div>`;
  if (subject.primaryEmail) html += `<div class="grid"><p><strong>Email:</strong></p><p>${sanitizeHTML(subject.primaryEmail)}</p></div>`;
  if (subject.primaryMobile) html += `<div class="grid"><p><strong>Mobile:</strong></p><p>${sanitizeHTML(subject.primaryMobile)}</p></div>`;
  html += `</div>`;

  const questionsToDisplay = report.questions || [];
  const questionsByPlatform = {};
  questionsToDisplay.forEach(q => {
    if (!q) return;
    const platformKey = q.platform || 'General';
    if (!questionsByPlatform[platformKey]) questionsByPlatform[platformKey] = [];
    questionsByPlatform[platformKey].push(q);
  });

  if (Object.keys(questionsByPlatform).length > 0) {
     html += `<div class="section"><h2>Detailed Findings by Platform</h2>`;
     for (const platform in questionsByPlatform) {
       html += `<div class="platform-card"><h3>Platform: ${sanitizeHTML(platform.toUpperCase())}</h3>`;
       const platformScores = report.reportScores?.platforms?.[platform];
       if (platformScores) {
         html += `<p><strong>Platform Score (Unweighted):</strong> ${platformScores.unweighted_platform_score_rounded?.toFixed(2) || 'N/A'}</p>`;
         html += `<p><strong>Platform Score (Weighted):</strong> ${platformScores.weighted_platform_score_rounded?.toFixed(2) || 'N/A'}</p>`;
       }
       if (questionsByPlatform[platform].length === 0) {
         html += `<p class="empty-section">No questions for this platform in this report type.</p>`;
       }
       questionsByPlatform[platform].forEach(q => {
         if (!q) return;
         const answer = report.answers?.find(a => a.questionId === q.id);
         html += `<div class="question-block"><p><strong>Q: ${sanitizeHTML(q.question)}</strong></p>`;
         html += answer ? `<div class="answer-block">A: ${sanitizeHTML(answer.answer)}</div>` : '<div class="answer-block empty-section"><em>Not Answered</em></div>';

         if (answer?.proofs && answer.proofs.length > 0) {
           answer.proofs.forEach(p => {
             html += `<div class="proof-block"><strong>Proof:</strong> ${sanitizeHTML(p.comment)}\`;
             if (p.filePath) {
               html += `<span class="proof-file-link">File: ${sanitizeHTML(p.originalFilename || 'Attached File')} (${sanitizeHTML(p.mimeType || 'N/A')})</span>\`;
             }
             if (p.behaviourScores && typeof p.behaviourScores === 'object' && Object.keys(p.behaviourScores).length > 0) {
               html += \`<ul class="behaviour-scores-list">\`;
               Object.entries(p.behaviourScores).forEach(([key, value]) => {
                 html += \`<li>${sanitizeHTML(key.replace(/_/g, ' '))}: ${sanitizeHTML(String(value))}</li>\`;
               });
               html += \`</ul>\`;
             }
             html += \`</div>\`;
           });
         } else if (answer) {
              html += \`<p class="empty-section proof-block">No proofs for this answer.</p>\`;
         }
         html += \`</div>\`;
       });
       html += \`</div>\`;
     }
     html += \`</div>\`;
  } else {
     html += `<div class="section"><h2>Detailed Findings by Platform</h2><p class="empty-section">No questions/platforms applicable for this report.</p></div>\`;
  }

  if (report.comments && report.comments.length > 0) {
     html += `<div class="section comment-block"><h2>General Report Comments</h2>\`;
     report.comments.forEach(comment => {
         html += `<div class="comment-item"><p>${sanitizeHTML(comment.comment)}</p><p class="comment-meta">By: ${sanitizeHTML(comment.commentBy?.firstName) || 'User'} ${sanitizeHTML(comment.commentBy?.lastName) || ''} on ${formatDate(comment.createdAt)}</p></div>\`;
     });
     html += \`</div>\`;
  } else {
     html += `<div class="section comment-block"><h2>General Report Comments</h2><p class="empty-section">No general comments for this report.</p></div>\`;
  }

  if (finalDisclaimer) html += `<div class="section disclaimer"><h2>Disclaimer</h2><p>${sanitizeHTML(finalDisclaimer)}</p></div>\`;
  html += `</div></body></html>`;
  return { html, finalFooterLink, finalCompanyNameForFooter, footerLogoUrlToUse: footerLogoUrl }; // Return determined values for footer
};

export const generateReportPDF = async (reportId) => {
  const report = await prisma.report.findUnique({
    where: { id: reportId },
    include: {
      subject: { include: { company: true, country: true, profiles: true } },
      company: true,
      answers: {
        orderBy: { createdAt: 'asc' },
        include: {
          question: { orderBy: [{platform: 'asc'}, {orderNumber: 'asc'}] },
          proofs: { orderBy: { createdAt: 'asc' } },
        },
      },
      comments: {
        orderBy: { createdAt: 'asc' },
        include: { commentBy: { select: { firstName: true, lastName: true, email: true } } },
      },
      questions: {
         orderBy: [{platform: 'asc'}, {orderNumber: 'asc'}]
      }
    },
  });

  if (!report || !report.subject) {
    throw new Error('Report or associated Subject not found for PDF generation.');
  }

  const defaultBrandingSettings = await prisma.defaultBranding.findFirst();
  const companyForBranding = report.company || report.subject.company;

  // Get values determined by getReportHTML for footer
  const { html: htmlContent, finalFooterLink, finalCompanyNameForFooter, footerLogoUrlToUse } = getReportHTML(report, report.subject, companyForBranding, defaultBrandingSettings);

  let browser;
  try {
    const executablePath = process.env.CHROME_BIN || undefined;
    const puppeteerArgs = ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage', '--font-render-hinting=none', '--single-process'];

    browser = await puppeteer.launch({
      headless: true,
      args: puppeteerArgs,
      executablePath
    });
    const page = await browser.newPage();
    await page.setContent(htmlContent, { waitUntil: 'networkidle0' });

    let footerHTML = \`<div style="font-size:8pt; width:100%; text-align:center; padding: 0 15mm 5mm 15mm; box-sizing:border-box;">\`;
    if (finalFooterLink) {
        footerHTML += \`<a href="\${sanitizeHTML(finalFooterLink)}" style="color:#0066cc;text-decoration:none;">\${sanitizeHTML(finalFooterLink)}</a><br/>\`;
    }
    footerHTML += \`&copy; ${new Date().getFullYear()} ${sanitizeHTML(finalCompanyNameForFooter)}. All rights reserved. \`;
    footerHTML += \`Page <span class="pageNumber"></span> of <span class="totalPages"></span></div>\`;

    const appBaseUrl = process.env.BACKEND_URL || 'http://localhost:3001';
    let headerImageHTML = '';
    if (footerLogoUrlToUse) { // Using the determined footerLogoUrl for header as per prompt example
        headerImageHTML = \`<img src="\${sanitizeHTML(footerLogoUrlToUse)}" style="height:30px; opacity:0.7; display:block; margin:0 auto;">\`;
    }


    const pdfBuffer = await page.pdf({
      format: 'A4',
      printBackground: true,
      displayHeaderFooter: true,
      footerTemplate: footerHTML,
      headerTemplate: \`<div style="width:100%; text-align:center; padding:10mm 15mm 0 15mm;">\${headerImageHTML}</div>\`,
      margin: { top: '30mm', right: '15mm', bottom: '25mm', left: '15mm' }
    });
    return pdfBuffer;
  } catch (error) {
    console.error("Error PDF gen:", error);
    // Add more specific error logging if possible
    if (error.message.includes('Failed to launch browser')) {
        console.error("Puppeteer launch error - check CHROME_BIN or environment setup.");
    }
    throw error;
  }
  finally {
    if (browser) await browser.close();
  }
};
