import { calculateAndSaveReportScores } from '../../src/services/reportScoreService';
import prisma from '../../src/utils/prismaClient';

jest.mock('../../src/utils/prismaClient', () => ({
    report: {
        findUnique: jest.fn(),
        update: jest.fn(),
    },
    globalWeights: {
        findMany: jest.fn(),
    },
    systemConfig: {
        findMany: jest.fn(),
    },
    // Add other models if needed for deeper includes not covered by direct mocks
}));

describe('Report Score Service - calculateAndSaveReportScores', () => {
    beforeEach(() => {
        // Reset all mock implementations and call counts
        (prisma.report.findUnique as jest.Mock).mockReset();
        (prisma.report.update as jest.Mock).mockReset();
        (prisma.globalWeights.findMany as jest.Mock).mockReset();
        (prisma.systemConfig.findMany as jest.Mock).mockReset();
    });

    it('should calculate scores correctly for a basic scenario with one platform and one answer', async () => {
        const mockReportId = 'report1';
        const mockQuestionId = 'q1';
        const mockPlatform = 'facebook';

        (prisma.report.findUnique as jest.Mock).mockResolvedValue({
            id: mockReportId,
            subjectId: 'subject1',
            requestType: 'standard',
            status: 'under_investigation',
            subject: {
                id: 'subject1',
                profiles: [{ platform: mockPlatform, valid: true }],
                company: { allowTrait: false } // Trait analysis disabled for this test
            },
            answers: [
                {
                    id: 'ans1', questionId: mockQuestionId, reportId: mockReportId, answer: 'yes', score: '5',
                    question: { id: mockQuestionId, platform: mockPlatform, answerType: 'yes_no', answerScore: ['0', '5'] }, // prePlatform/postPlatform not on question
                    proofs: []
                }
            ],
            questions: [ // Curated list of questions for the report
                { id: mockQuestionId, platform: mockPlatform, question: 'Is this a test?', answerType: 'yes_no', answerScore: ['0', '5'], orderNumber: 1 }
            ]
        });

        (prisma.globalWeights.findMany as jest.Mock).mockResolvedValue([
            { socialPlatform: mockPlatform, globalUsageWeighting: 1.0 }
        ]);

        (prisma.systemConfig.findMany as jest.Mock).mockResolvedValue([
            { opt: 'pre_platform_scoring_metric', val: '5' }, // Max score per question
            { opt: 'post_platform_scoring_metric', val: '5' }, // Scale factor for platform score
            { opt: 'social_media_max_score', val: '900' },
            { opt: 'behavior_weighting', val: '0.08' }
        ]);

        // Mock the update call to just return the data it was called with
        (prisma.report.update as jest.Mock).mockImplementation(async ({ where, data }) => ({
            id: where.id,
            ...data
        }));

        const result = await calculateAndSaveReportScores(mockReportId);

        expect(prisma.report.update).toHaveBeenCalledTimes(1);
        expect(result.riskScore).toBeDefined();
        expect(result.reportScores).toBeDefined();

        const platformScores = result.reportScores.platforms;
        expect(platformScores[mockPlatform]).toBeDefined();
        // With score 5, prePlatformMetric 5, postPlatformScoringMetric 5:
        // unweightedPlatformScore = (5 / (1 * 5)) * 5 = 5
        expect(platformScores[mockPlatform].unweighted_platform_score_rounded).toEqual(5.00);

        // activePlatformsForSubject = {facebook}, totalGlobalUsageWeightingForActivePlatforms = 1.0
        // currentPlatformGlobalWeight = 1.0
        // weightedPlatformScore = (1.0 / 1.0) * 5 = 5
        expect(platformScores[mockPlatform].weighted_platform_score_rounded).toEqual(5.00);

        // sumWeightedPlatformScores = 5
        // weightedSocialMediaScore = (5 / 5) * 900 = 900
        expect(result.reportScores.weighted_social_media_score_round).toEqual(900);

        // finalRiskScore = (1 - (900 / 900)) * 100 = 0
        expect(result.riskScore).toEqual(0.00);

        expect(result.socialMediaScores).toBeDefined();
        // Check if defaultName logic for socialMediaScores is handled (not in this simple Q/A data)
    });

    it('should handle a scenario with no answers', async () => {
        (prisma.report.findUnique as jest.Mock).mockResolvedValue({
            id: 'report2', subjectId: 'subject2', requestType: 'standard',
            subject: { id: 'subject2', profiles: [{ platform: 'facebook', valid: true }], company: {allowTrait: false} },
            answers: [], // No answers
            questions: [ { id: 'q1', platform: 'facebook', question: 'Q1?', answerType: 'yes_no', answerScore: ['0', '5'], orderNumber: 1 } ]
        });
        (prisma.globalWeights.findMany as jest.Mock).mockResolvedValue([{ socialPlatform: 'facebook', globalUsageWeighting: 1.0 }]);
        (prisma.systemConfig.findMany as jest.Mock).mockResolvedValue([
            { opt: 'pre_platform_scoring_metric', val: '5' },
            { opt: 'post_platform_scoring_metric', val: '5' },
            { opt: 'social_media_max_score', val: '900' },
        ]);
        (prisma.report.update as jest.Mock).mockImplementation(async ({ data }) => ({ id: 'report2', ...data }));

        const result = await calculateAndSaveReportScores('report2');
        expect(result.riskScore).toBeDefined();
        // Platform score should be 0 if no answers for its questions
        expect(result.reportScores.platforms.facebook.unweighted_platform_score_rounded).toEqual(0);
        expect(result.reportScores.platforms.facebook.weighted_platform_score_rounded).toEqual(0);
        // Risk score should be 100 if social media score is 0
        expect(result.riskScore).toEqual(100.00);
    });

    // Add more tests: multiple platforms, behavior scores, no active profiles, etc.
  });
