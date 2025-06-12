import prisma from '../utils/prismaClient.js';

// Constants for platform names (assuming these are consistent)
const PROFILE_PLATFORMS = ['twitter', 'facebook', 'instagram', 'pinterest', 'linkedin', 'flickr', 'youtube', 'web'];


const getSystemConfigValue = (configs, opt, defaultValue, isFloat = true) => {
  const config = configs.find(c => c.opt === opt);
  if (!config || config.val === null || config.val === undefined) return defaultValue;
  // Ensure val is treated as string for parseFloat, or handle if it's already number
  const valueToParse = typeof config.val === 'string' ? config.val : String(config.val);
  return isFloat ? parseFloat(valueToParse) : valueToParse;
};

const getPlatformWeight = (weights, platformName) => {
  const lowerPlatformName = platformName?.toLowerCase();
  const weight = weights.find(w => w.socialPlatform?.toLowerCase() === lowerPlatformName);
  // Default to a small non-zero weight if platform exists but has no specific weight,
  // or 0 if platform itself is not in global weights.
  return weight ? parseFloat(weight.globalUsageWeighting.toString()) : 0.1;
};

// Helper to check score value boundaries (from original PHP)
 const checkScoreValue = (value, min = -5.0, max = 5.0) => {
     const numValue = parseFloat(String(value)); // Ensure it's a number
     if (isNaN(numValue)) return 0; // Default for invalid numbers
     if (numValue >= max) return max;
     if (numValue <= min) return min;
     return parseFloat(numValue.toFixed(2)); // Keep precision
 };


export const calculateAndSaveReportScores = async (reportId) => {
  const report = await prisma.report.findUnique({
    where: { id: reportId },
    include: {
      answers: {
        include: {
          question: true,
          proofs: true // behaviourScores are on Proof
        }
      },
      subject: { include: { profiles: true, company: true } },
      questions: { // Fetch curated questions as well
        orderBy: [{platform: 'asc'}, {orderNumber: 'asc'}]
      }
    },
  });

  if (!report || !report.subject) {
    throw new Error('Report or Subject not found for score calculation.');
  }

  const allGlobalWeights = await prisma.globalWeights.findMany();
  const allSystemConfigs = await prisma.systemConfig.findMany();

  const prePlatformScoringMetric = getSystemConfigValue(allSystemConfigs, 'pre_platform_scoring_metric', 30.0);
  const postPlatformScoringMetric = getSystemConfigValue(allSystemConfigs, 'post_platform_scoring_metric', 5.0);
  const socialMediaMaxScore = getSystemConfigValue(allSystemConfigs, 'social_media_max_score', 900.0);
  const behaviorWeightingConstant = getSystemConfigValue(allSystemConfigs, 'behavior_weighting', 0.08);
  // const behaviorAverage = getSystemConfigValue(allSystemConfigs, 'behavior_average', 2.5); // For behavior score final step

  const calculatedPlatformScores = {};
  let sumWeightedPlatformScores = 0;
  const allBehaviorScoresAggregatedForOverall = {};

  const activePlatformsForSubject = new Set<string>();
  report.subject.profiles.forEach(profile => {
    if (profile.valid && profile.platform) {
      activePlatformsForSubject.add(profile.platform.toLowerCase());
    }
  });

  let totalGlobalUsageWeightingForActivePlatforms = 0;
  activePlatformsForSubject.forEach(platform => {
     totalGlobalUsageWeightingForActivePlatforms += getPlatformWeight(allGlobalWeights, platform);
  });

  const platformsInReport = new Set<string>();
  (report.questions || []).forEach(q => { // Use curated report.questions
     if(q.platform) platformsInReport.add(q.platform.toLowerCase());
  });
  // Fallback: if report.questions is empty, derive from answers (though ideally backend provides curated questions)
  if (platformsInReport.size === 0) {
    report.answers?.forEach(ans => {
        if (ans.question?.platform) platformsInReport.add(ans.question.platform.toLowerCase());
    });
  }


  if (totalGlobalUsageWeightingForActivePlatforms === 0) {
    if (activePlatformsForSubject.size > 0) {
      totalGlobalUsageWeightingForActivePlatforms = activePlatformsForSubject.size * 0.1;
    } else if (platformsInReport.size > 0) { // No active profiles, use platforms with answers
        let tempWeightSum = 0;
        platformsInReport.forEach(p => tempWeightSum += getPlatformWeight(allGlobalWeights, p));
        totalGlobalUsageWeightingForActivePlatforms = tempWeightSum > 0 ? tempWeightSum : platformsInReport.size * 0.1; // default if all weights are 0
    } else {
        totalGlobalUsageWeightingForActivePlatforms = 1;
    }
  }


  for (const platform of platformsInReport) {
    if (!platform) continue;

    let platformAnswerScoreSum = 0;
    let questionsOnThisPlatformCount = 0;
    const platformSpecificBehaviorScoresSum = {};

    // Iterate over report.questions for this platform instead of answers, to ensure all questions are considered
    const questionsForPlatform = (report.questions || []).filter(q => q.platform?.toLowerCase() === platform || q.platform?.toLowerCase() === 'all');

    questionsForPlatform.forEach(q => {
      questionsOnThisPlatformCount++;
      const answer = report.answers?.find(ans => ans.questionId === q.id);
      let questionScore = 0;

      if (answer) { // If there's an answer for this question
        if (answer.score !== null && answer.score !== undefined) {
          questionScore = parseFloat(answer.score);
        } else if (q.answerType === 'yes_no' && q.answerScore && Array.isArray(q.answerScore) && q.answerScore.length === 2) {
          questionScore = answer.answer === 'yes' ? parseFloat(q.answerScore[1]) : parseFloat(q.answerScore[0]);
        } else if (q.answerType === 'multiple_choice' && q.answerOptions && Array.isArray(q.answerOptions) && q.answerScore && Array.isArray(q.answerScore)) {
          const optionIndex = q.answerOptions.indexOf(answer.answer || '');
          if (optionIndex !== -1 && q.answerScore[optionIndex] !== undefined) {
            questionScore = parseFloat(q.answerScore[optionIndex]);
          }
        }
        // Slider logic might be needed here if ans.score doesn't already reflect it.
        // For now, assume ans.score is the determined value for slider questions if applicable.

        if (report.subject.company?.allowTrait && answer.proofs) {
          answer.proofs.forEach(proof => {
            if (proof.behaviourScores && typeof proof.behaviourScores === 'object') {
              for (const [key, value] of Object.entries(proof.behaviourScores as Record<string, number>)) {
                const weightedValue = value * behaviorWeightingConstant;
                platformSpecificBehaviorScoresSum[key] = (platformSpecificBehaviorScoresSum[key] || 0) + weightedValue;
                allBehaviorScoresAggregatedForOverall[key] = (allBehaviorScoresAggregatedForOverall[key] || 0) + weightedValue;
              }
            }
          });
        }
      } // If no answer, questionScore remains 0, contributing to the average if prePlatformScoringMetric is seen as max score.
      platformAnswerScoreSum += questionScore;
    });

    const unweightedPlatformScore = questionsOnThisPlatformCount > 0 ?
      (platformAnswerScoreSum / (questionsOnThisPlatformCount * prePlatformScoringMetric)) * postPlatformScoringMetric : 0;

    let weightedPlatformScore = 0;
    if (activePlatformsForSubject.has(platform)) {
         const currentPlatformGlobalWeight = getPlatformWeight(allGlobalWeights, platform);
         weightedPlatformScore = (currentPlatformGlobalWeight / totalGlobalUsageWeightingForActivePlatforms) * unweightedPlatformScore;
    } else if (platformsInReport.size > 0 && !activePlatformsForSubject.size) {
         // If no validated profiles, but answers exist, distribute weight based on its own weight relative to other answered platforms
         const currentPlatformGlobalWeight = getPlatformWeight(allGlobalWeights, platform);
         weightedPlatformScore = (currentPlatformGlobalWeight / totalGlobalUsageWeightingForActivePlatforms) * unweightedPlatformScore;
    }

    const platformBehaviorScoresFinal = {};
    if (report.subject.company?.allowTrait) {
        for(const [key, sumVal] of Object.entries(platformSpecificBehaviorScoresSum as Record<string,number>)){
             platformBehaviorScoresFinal[key] = checkScoreValue(sumVal + 2.5); // Adding 2.5 as per PHP ($value + $behavior_average)
        }
    }

    calculatedPlatformScores[platform] = {
      unweighted_platform_score: parseFloat(unweightedPlatformScore.toFixed(6)),
      unweighted_platform_score_rounded: parseFloat(unweightedPlatformScore.toFixed(2)),
      weighted_platform_score: parseFloat(weightedPlatformScore.toFixed(6)),
      weighted_platform_score_rounded: parseFloat(weightedPlatformScore.toFixed(2)),
      behavior_scores: platformBehaviorScoresFinal,
    };
    sumWeightedPlatformScores += weightedPlatformScore;
  }

  const overallBehaviorScoresResult = {};
  if (report.subject.company?.allowTrait) {
     const numPlatformsWithBehaviorData = Object.values(calculatedPlatformScores).filter(p => p.behavior_scores && Object.keys(p.behavior_scores).length > 0).length || 1;
     for (const [key, totalWeightedValue] of Object.entries(allBehaviorScoresAggregatedForOverall as Record<string, number>)) {
         const averageWeightedValue = totalWeightedValue / numPlatformsWithBehaviorData;
         overallBehaviorScoresResult[key] = checkScoreValue(averageWeightedValue + 2.5);
     }
  }

  // Max possible sum of weighted scores assuming weights normalize to 1 and each platform can score up to postPlatformScoringMetric
  const maxPossibleSumWeightedScores = postPlatformScoringMetric * (activePlatformsForSubject.size || 1); // Or just postPlatformScoringMetric if sumWeightedPlatformScores is already a normalized average
                                                                                                          // The PHP logic implies sumWeightedPlatformScores is a sum of scores that are already weighted relative to each other.
                                                                                                          // If sum of weights is 1, then max sumWeightedPlatformScores is postPlatformScoringMetric.

  const weightedSocialMediaScoreRaw = socialMediaMaxScore > 0 && postPlatformScoringMetric > 0 ?
    (sumWeightedPlatformScores / postPlatformScoringMetric) * socialMediaMaxScore : 0; // Simplified: if sum of weighted scores is up to postPlatformScoringMetric, scale to socialMediaMaxScore

  const weightedSocialMediaScore = parseFloat(weightedSocialMediaScoreRaw.toFixed(6));
  const weightedSocialMediaScoreRound = parseFloat(weightedSocialMediaScore.toFixed(2));

  const finalRiskScoreRaw = socialMediaMaxScore > 0 ? (1 - (Math.min(weightedSocialMediaScore, socialMediaMaxScore) / socialMediaMaxScore)) * 100 : 100;
  const finalRiskScore = Math.max(0, Math.min(100, parseFloat(finalRiskScoreRaw.toFixed(2))));

  const socialMediaScoresCalculated = {};
  report.answers?.forEach(ans => {
     if (ans.question?.defaultName && ans.question.platform && PROFILE_PLATFORMS.includes(ans.question.platform.toLowerCase())) {
         const platformKey = ans.question.platform.toLowerCase();
         if (!socialMediaScoresCalculated[platformKey]) {
             socialMediaScoresCalculated[platformKey] = {};
         }
         socialMediaScoresCalculated[platformKey][ans.question.defaultName.replace(`${platformKey}_`, '')] = ans.score || "0";
     }
  });

  const updatedReport = await prisma.report.update({
    where: { id: reportId },
    data: {
      reportScores: {
        platforms: calculatedPlatformScores,
        overall_behavior_scores: overallBehaviorScoresResult,
        weighted_social_media_score: weightedSocialMediaScore,
        weighted_social_media_score_round: weightedSocialMediaScoreRound,
        risk_score_pre_round: parseFloat(finalRiskScoreRaw.toFixed(6)),
      },
      socialMediaScores: socialMediaScoresCalculated,
      riskScore: finalRiskScore,
      updatedAt: new Date(),
    },
  });
  return updatedReport;
};
