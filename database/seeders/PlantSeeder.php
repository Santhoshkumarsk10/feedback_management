<?php

namespace Database\Seeders;

use App\Models\Feedback;
use App\Models\Question;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlantSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Setup Admin Accounts
        User::updateOrCreate(['email' => 'superadmin@plant.test'], [
            'name' => 'Super Admin',
            'password' => 'password',
            'role' => 'superadmin',
            'department' => 'Management',
            'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'admin@plant.test'], [
            'name' => 'Admin',
            'password' => 'password',
            'role' => 'admin',
            'department' => 'Technical Centre',
            'is_active' => true,
        ]);

        // 2. Setup Shibaura Technical Centre Organizers / SMI Engineers
        $organizersData = [
            ['Ravi Kumar', 'ravi@plant.test', '9100000001', 'Injection Molding Demo'],
            ['Priya S', 'priya@plant.test', '9100000002', 'Customer Mould Trial'],
            ['Karthik M', 'karthik@plant.test', '9100000003', 'Machine Tools & Training'],
            ['Suresh Raina', 'suresh@plant.test', '9100000004', 'Die Casting & VR Zone'],
        ];

        foreach ($organizersData as [$n, $e, $m, $d]) {
            User::updateOrCreate(['email' => $e], [
                'name' => $n,
                'mobile' => $m,
                'password' => 'password',
                'role' => 'organizer',
                'department' => $d,
                'is_active' => true,
            ]);
        }

        // 3. Official Shibaura Machine India Technical Centre Feedback Questions (Section-wise)
        $shibauraQuestions = [
            // Section 1 — Visit Details / Product & Purpose
            [
                'section' => 'Section 1 — Visit Details & Purpose',
                'question' => 'Product Interested',
                'type' => 'mcq',
                'options' => ['Injection Molding Machine', 'Auxiliary Equipment', 'Machine Tools', 'Die Casting Machine', 'machiNETCloud'],
                'is_required' => false,
            ],
            [
                'section' => 'Section 1 — Visit Details & Purpose',
                'question' => 'Purpose of Visit',
                'type' => 'mcq',
                'options' => ['Machine Evaluation', 'Mould Trial', 'Product Demonstration', 'Technical Discussion', 'Customer Training', 'VR Experience', 'Other'],
                'is_required' => true,
            ],

            // Section 2 — Overall Experience (Likert 1-5)
            [
                'section' => 'Section 2 — Overall Experience',
                'question' => 'How would you rate your overall experience at the Technical Centre?',
                'type' => 'rating',
                'options' => null,
                'is_required' => true,
            ],
            [
                'section' => 'Section 2 — Overall Experience',
                'question' => 'How well did the Technical Centre meet the objective of your visit?',
                'type' => 'rating',
                'options' => null,
                'is_required' => true,
            ],
            [
                'section' => 'Section 2 — Overall Experience',
                'question' => 'How would you rate the technical support provided by our team?',
                'type' => 'rating',
                'options' => null,
                'is_required' => true,
            ],
            [
                'section' => 'Section 2 — Overall Experience',
                'question' => 'How would you rate the quality and relevance of the demonstrations?',
                'type' => 'rating',
                'options' => null,
                'is_required' => true,
            ],

            // Section 3 — Facilities & Activities Experienced
            [
                'section' => 'Section 3 — Facilities & Activities Experienced',
                'question' => 'Which facilities / activities did you experience during your visit?',
                'type' => 'mcq',
                'options' => ['Live Machine Demonstration', 'Customer Mould Trial', 'Customer Training Program', 'VR Zone', 'Product / Technology Presentation', 'Technical Discussion'],
                'is_required' => true,
            ],
            [
                'section' => 'Section 3 — Facilities & Activities Experienced',
                'question' => 'Live Machine Demo: How valuable was the live machine demonstration in helping you understand capabilities?',
                'type' => 'rating',
                'options' => null,
                'is_required' => false,
            ],
            [
                'section' => 'Section 3 — Facilities & Activities Experienced',
                'question' => 'Live Machine Demo: Did the demonstration help you evaluate the machine for your application?',
                'type' => 'mcq',
                'options' => ['Definitely', 'To some extent', 'Not really', 'Not applicable'],
                'is_required' => false,
            ],
            [
                'section' => 'Section 3 — Facilities & Activities Experienced',
                'question' => 'Customer Mould Trial: How valuable was running your own mould on our machine in evaluating the solution?',
                'type' => 'rating',
                'options' => null,
                'is_required' => false,
            ],
            [
                'section' => 'Section 3 — Facilities & Activities Experienced',
                'question' => 'Customer Mould Trial: Did the mould trial provide sufficient technical information for decision-making?',
                'type' => 'mcq',
                'options' => ['Yes, completely', 'Yes, to some extent', 'No', 'Too early to evaluate'],
                'is_required' => false,
            ],
            [
                'section' => 'Section 3 — Facilities & Activities Experienced',
                'question' => 'VR Zone: How useful was the VR experience in helping you understand our products & features?',
                'type' => 'rating',
                'options' => null,
                'is_required' => false,
            ],
            [
                'section' => 'Section 3 — Facilities & Activities Experienced',
                'question' => 'VR Zone: Did the VR experience improve your understanding of the product / technology?',
                'type' => 'mcq',
                'options' => ['Significantly', 'Moderately', 'Slightly', 'Not at all'],
                'is_required' => false,
            ],

            // Section 4 — Technical Centre Facilities Rating (1-5)
            [
                'section' => 'Section 4 — Technical Centre Facilities Rating',
                'question' => 'Facilities Rating: Facility & Infrastructure',
                'type' => 'rating',
                'options' => null,
                'is_required' => true,
            ],
            [
                'section' => 'Section 4 — Technical Centre Facilities Rating',
                'question' => 'Facilities Rating: Machine Demonstration Area',
                'type' => 'rating',
                'options' => null,
                'is_required' => true,
            ],
            [
                'section' => 'Section 4 — Technical Centre Facilities Rating',
                'question' => 'Facilities Rating: VR Zone',
                'type' => 'rating',
                'options' => null,
                'is_required' => true,
            ],
            [
                'section' => 'Section 4 — Technical Centre Facilities Rating',
                'question' => 'Facilities Rating: Training Facilities',
                'type' => 'rating',
                'options' => null,
                'is_required' => true,
            ],
            [
                'section' => 'Section 4 — Technical Centre Facilities Rating',
                'question' => 'Facilities Rating: Cleanliness & Presentation',
                'type' => 'rating',
                'options' => null,
                'is_required' => true,
            ],
            [
                'section' => 'Section 4 — Technical Centre Facilities Rating',
                'question' => 'Facilities Rating: Overall Professional Experience',
                'type' => 'rating',
                'options' => null,
                'is_required' => true,
            ],

            // Section 5 — Impact of Your Visit
            [
                'section' => 'Section 5 — Impact of Your Visit',
                'question' => 'After your visit, how has your confidence in Shibaura Machine\'s technology changed?',
                'type' => 'mcq',
                'options' => ['Significantly Increased', 'Increased', 'No Change', 'Decreased'],
                'is_required' => true,
            ],
            [
                'section' => 'Section 5 — Impact of Your Visit',
                'question' => 'How likely are you to consider Shibaura Machine for your machine requirements?',
                'type' => 'mcq',
                'options' => ['5 - Extremely likely', '4 - Very likely', '3 - Quite likely', '2 - Moderately likely', '1 - Slightly likely', '0 - Not at all likely'],
                'is_required' => true,
            ],

            // Section 6 — Recommendation
            [
                'section' => 'Section 6 — Recommendation',
                'question' => 'Would you recommend the Shibaura Machine Technical Centre to colleagues / associates?',
                'type' => 'mcq',
                'options' => ['Definitely', 'Probably', 'Not Sure', 'Probably Not', 'Definitely Not'],
                'is_required' => true,
            ],

            // Section 7 — Customer Feedback
            [
                'section' => 'Section 7 — Customer Feedback & Suggestions',
                'question' => 'Any additional suggestions, feedback, or comments for improvement?',
                'type' => 'text',
                'options' => null,
                'is_required' => false,
            ],
        ];

        // Clean out previous questions & insert section-wise questions
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Feedback::truncate();
        Visit::truncate();
        Question::truncate();
        DB::table('feedback_answers')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        foreach ($shibauraQuestions as $i => $item) {
            Question::create([
                'section' => $item['section'],
                'question' => $item['question'],
                'type' => $item['type'],
                'options' => $item['options'],
                'is_required' => $item['is_required'],
                'is_active' => true,
                'sort_order' => $i + 1,
            ]);
        }

        // 4. Generate realistic demo visit and feedback data for Shibaura Machine
        $organizers = User::where('role', 'organizer')->get();
        $questions = Question::active()->get();

        $clientCompanies = [
            'Motherson Sumi Systems', 'Tata AutoComp Systems', 'Varroc Engineering',
            'Supreme Industries', 'Nilkamal Plastics', 'Craftsman Automation',
            'Sundram Fasteners', 'Jay Bharat Maruti', 'Lumax Industries', 'Subros Limited'
        ];

        $visitorNames = [
            'Anand Natarajan', 'Venkatesh Iyer', 'Rajesh Sharma', 'Kavitha R',
            'Siddharth Patel', 'Murugan Swamy', 'Deepak Chopra', 'Manish Reddy',
            'Arunachalam S', 'Karthikeyan P', 'Vijay Raghavan', 'Balaji Krishnan',
            'Sunil Narang', 'Rohit Verma', 'Bhavna Joshi', 'Harish Babu'
        ];

        $sampleComments = [
            'Live demonstration of the all-electric injection molding machine was very impressive. Team explained precision control thoroughly.',
            'Mould trial results met our cycle-time and part-weight specifications. Highly satisfied with technical capability.',
            'VR Zone was an innovative way to visualize machine internals and maintenance access. Very informative session.',
            'Technical discussion was very productive. Looking forward to quotation for the die casting machine.',
            'Good infrastructure and well-maintained testing centre. Training facilities are top-notch.',
            'Mould trial was good, but cycle time optimization can be tuned further. Need follow-up meeting with tooling team.',
            'Excellent presentation and hospitality by the Shibaura engineering team. Confidence in technology is significantly increased.',
        ];

        foreach (range(1, 35) as $i) {
            $organizer = $organizers->random();
            $visitorName = $visitorNames[array_rand($visitorNames)].' '.rand(1, 99);
            $company = $clientCompanies[array_rand($clientCompanies)];
            $date = now()->subDays(rand(1, 120))->toDateString();
            $rating = rand(1, 10) <= 2 ? rand(1, 2) : rand(4, 5); // realistic distribution with some low reviews

            $visit = Visit::create([
                'organizer_id' => $organizer->id,
                'visitor_name' => $visitorName,
                'visitor_mobile' => '9840'.rand(100000, 999999),
                'visitor_company' => $company,
                'visitor_email' => strtolower(str_replace(' ', '.', $visitorName)).'@'.strtolower(explode(' ', $company)[0]).'.com',
                'visit_date' => $date,
                'purpose' => collect(['Machine Evaluation', 'Mould Trial', 'Product Demonstration', 'VR Experience', 'Technical Discussion'])->random(),
            ]);

            $fb = Feedback::create([
                'visit_id' => $visit->id,
                'organizer_id' => $organizer->id,
                'overall_rating' => $rating,
                'comments' => $rating <= 2 ? 'Mould trial faced minor temperature fluctuation issues. Needs deeper review.' : $sampleComments[array_rand($sampleComments)],
                'submitted_at' => $date.' '.rand(10, 17).':'.rand(10, 59).':00',
            ]);

            foreach ($questions as $q) {
                $answer = null;
                if ($q->type === 'rating') {
                    $answer = (string) max(1, min(5, $rating + rand(-1, 0)));
                } elseif ($q->type === 'mcq' && $q->options) {
                    $answer = $q->options[array_rand($q->options)];
                } elseif ($q->type === 'text') {
                    $answer = $rating <= 2 ? 'Please optimize trial scheduling and technical documentation turnaround time.' : 'Excellent support from Shibaura engineering team.';
                }

                $fb->answers()->create([
                    'question_id' => $q->id,
                    'answer' => $answer,
                ]);
            }
        }
    }
}
