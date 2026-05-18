const fs = require('fs');
const path = require('path');

const projectIds = ['2787286815423493552', '2787286815423498836'];
const efcCodes = ['1592913036', '2397410445', '4126798990'];

const screens = [
  { id: 'c812e32bb65947d8a249a3a6cedc2e62', name: 'login_register' },
  { id: 'e9bc3ff249d849cbb73c996817ab5668', name: 'landing_page' },
  { id: '1b312fa183bb4622b68ea065dbd8ecce', name: 'dashboard' },
  { id: '6b35c453bebd4276972f2f7e0be2a8ad', name: 'reports_export' },
  { id: '499d1b916c7f43fdac74bbc3caf37a1e', name: 'stock_control' },
  { id: '5a494fbda5424b81aaa43893b572ed48', name: 'superadmin_control' },
  { id: '0683f84d25a440d1a770fb9e25789d02', name: 'quick_command_panel' }
];

const outputBaseDir = path.join(__dirname, '..', 'stitch_screens');

async function downloadFile(url, destPath, isBinary = false) {
  const response = await fetch(url);
  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }
  if (isBinary) {
    const arrayBuffer = await response.arrayBuffer();
    const buffer = Buffer.from(arrayBuffer);
    fs.writeFileSync(destPath, buffer);
  } else {
    const text = await response.text();
    fs.writeFileSync(destPath, text, 'utf8');
  }
}

async function main() {
  if (!fs.existsSync(outputBaseDir)) {
    fs.mkdirSync(outputBaseDir, { recursive: true });
  }

  console.log(`Starting stitch screen downloader...`);
  console.log(`Project IDs to try: ${projectIds.join(', ')}`);
  console.log(`EFC Codes to try: ${efcCodes.join(', ')}`);
  console.log(`Output Directory: ${outputBaseDir}\n`);

  for (const screen of screens) {
    console.log(`-----------------------------------------------`);
    console.log(`Processing Screen: ${screen.name} (${screen.id})`);
    let found = false;

    for (const projectId of projectIds) {
      if (found) break;
      for (const efc of efcCodes) {
        const codeUrl = `https://stitch-assets.web.app/${projectId}/${efc}/${screen.id}/code.html`;
        const pngUrl = `https://stitch-assets.web.app/${projectId}/${efc}/${screen.id}/screen.png`;

        console.log(`  Trying Project: ${projectId} with EFC: ${efc}...`);
        try {
          const checkRes = await fetch(codeUrl);
          if (checkRes.status === 200) {
            console.log(`  [SUCCESS] Found assets!`);
            
            const screenOutputDir = path.join(outputBaseDir, screen.name);
            if (!fs.existsSync(screenOutputDir)) {
              fs.mkdirSync(screenOutputDir, { recursive: true });
            }

            console.log(`    Saving code.html...`);
            const text = await checkRes.text();
            fs.writeFileSync(path.join(screenOutputDir, 'code.html'), text, 'utf8');

            console.log(`    Downloading screen.png...`);
            await downloadFile(pngUrl, path.join(screenOutputDir, 'screen.png'), true);

            console.log(`    Saved successfully to stitch_screens/${screen.name}/`);
            found = true;
            break; // Stop trying other combinations for this screen
          } else {
            // Check if maybe it's not HTML, print status
          }
        } catch (err) {
          console.log(`    Error: ${err.message}`);
        }
      }
    }

    if (!found) {
      console.log(`  [FAILED] Could not download screen: ${screen.name}`);
    }
  }

  console.log(`\nStitch screen downloader process finished!`);
}

main().catch(err => console.error('Global error:', err));
