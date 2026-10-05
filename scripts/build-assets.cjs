const fs=require('node:fs');
const path=require('node:path');
const target=path.join(__dirname,'../public/vendor/chart.js');fs.mkdirSync(target,{recursive:true});
for(const [src,dest] of [['dist/chart.umd.js','chart.umd.js'],['dist/chart.umd.js.map','chart.umd.js.map'],['LICENSE.md','LICENSE.md']]) {
    fs.copyFileSync(path.join(__dirname,'../node_modules/chart.js',src),path.join(target,dest));
}
console.log('Chart.js assets copied to public/vendor/chart.js');
