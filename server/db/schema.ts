export interface DatabaseSchema {
  members: {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    phone: string | null;
    membership_type: string;
    join_date: string;
    status: string;
    created_at: string;
  };
  teams: {
    id: number;
    name: string;
    sport: string;
    category: string;
    coach_name: string | null;
    description: string | null;
    created_at: string;
  };
  team_members: {
    id: number;
    team_id: number;
    member_id: number;
    position: string | null;
    joined_at: string;
  };
  events: {
    id: number;
    title: string;
    description: string | null;
    event_type: string;
    start_date: string;
    end_date: string | null;
    location: string | null;
    team_id: number | null;
    created_at: string;
  };
}
