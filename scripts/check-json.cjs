const fs = require('fs');

const files = [
  'src/components/search-results/module.json',
  'src/components/search-result-type/module.json',
];

for (const file of files) {
  JSON.parse(fs.readFileSync(file, 'utf8'));
  process.stdout.write(`OK ${file}\n`);
}
