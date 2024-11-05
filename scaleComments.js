const fs = require('fs');
const csv = require('csv-parser');
const Sentiment = require('sentiment');
const sentiment = new Sentiment();

const inputFile = process.argv[2];
if (!inputFile) {
    console.error('Please provide a CSV file path as an argument.');
    process.exit(1);
}

function mapToLikert(score) {
    if (score > 2) return 5; // Strongly Positive
    if (score > 0) return 4; // Positive
    if (score === 0) return 3; // Neutral
    if (score >= -2) return 2; // Negative
    return 1; // Strongly Negative
}

const commentsData = [];
const commentColumns = [
    'video1 comments',
    'video2 comments',
    'video3 comments',
    'video4 comments',
    'video5 comments'
];

fs.createReadStream(inputFile)
    .pipe(csv())
    .on('data', (row) => {
        commentColumns.forEach(column => {
            if (row[column]) {
                const comment = row[column];
                const result = sentiment.analyze(comment);
                const likertScore = mapToLikert(result.score);
                
                commentsData.push({
                    comment: comment,
                    sentimentScore: result.score,
                    likertScore: likertScore
                });
            }
        });
    })
    .on('end', () => {
        const outputFile = 'CommentsScale/commentsData.json';
        fs.writeFileSync(outputFile, JSON.stringify(commentsData, null, 2));
        console.log('Comments data has been saved to', outputFile);
    })
    .on('error', (err) => {
        console.error('Error reading the file:', err.message);
    });
