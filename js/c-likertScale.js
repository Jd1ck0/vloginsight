const fs = require('fs');
const csv = require('csv-parser');
const Sentiment = require('sentiment');
const sentiment = new Sentiment();

// Accept two CSV files as arguments
const inputFile1 = process.argv[2];
const inputFile2 = process.argv[3];

if (!inputFile1 || !inputFile2) {
    console.error('Please provide two CSV file paths as arguments.');
    process.exit(1);
}

function mapToLikert(score) {
    if (score > 2) return 5; // Strongly Positive
    if (score > 0) return 4; // Positive
    if (score === 0) return 3; // Neutral
    if (score >= -2) return 2; // Negative
    return 1; // Strongly Negative
}

// Initial structure to hold the comment data for each source file
const commentsData = {
    file1: {
        video1: [],
        video2: [],
        video3: [],
        video4: [],
        video5: []
    },
    file2: {
        video1: [],
        video2: [],
        video3: [],
        video4: [],
        video5: []
    }
};

const commentColumns = [
    'video1 comments',
    'video2 comments',
    'video3 comments',
    'video4 comments',
    'video5 comments'
];

function processFile(inputFile, fileKey) {
    return new Promise((resolve, reject) => {
        fs.createReadStream(inputFile)
            .pipe(csv())
            .on('data', (row) => {
                commentColumns.forEach(column => {
                    if (row[column] && row[column].trim()) {
                        const comment = row[column].trim();
                        const result = sentiment.analyze(comment);
                        const likertScore = mapToLikert(result.score);

                        // Get video number from column name and map it to videoX key
                        const videoKey = column.replace(' comments', '');
                        
                        // Store the processed comment in the appropriate video array for the correct file
                        commentsData[fileKey][videoKey].push({
                            comment: comment,
                            sentimentScore: result.score,
                            likertScore: likertScore,
                            sourceFile: fileKey
                        });
                    }
                });
            })
            .on('end', () => {
                console.log(`Finished processing file: ${inputFile}`);
                resolve();
            })
            .on('error', (err) => {
                console.error('Error reading the file:', err.message);
                reject(err);
            });
    });
}

async function processBothFiles() {
    try {
        await processFile(inputFile1, 'file1');
        await processFile(inputFile2, 'file2');
        
        // Output to a JSON file
        const outputFile = 'CommentsScale/c-commentsData.json';
        fs.writeFileSync(outputFile, JSON.stringify(commentsData, null, 2));
        console.log('Comments data has been saved to', outputFile);
    } catch (error) {
        console.error('An error occurred while processing the files:', error.message);
    }
}

processBothFiles();
