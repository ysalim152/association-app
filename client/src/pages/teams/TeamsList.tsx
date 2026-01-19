import * as React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

interface Team {
  id: number;
  name: string;
  sport: string;
  category: string;
  coach_name: string | null;
  description: string | null;
  created_at: string;
}

interface TeamsListProps {
  refreshTrigger: number;
}

export function TeamsList({ refreshTrigger }: TeamsListProps) {
  const [teams, setTeams] = React.useState<Team[]>([]);
  const [loading, setLoading] = React.useState(true);

  React.useEffect(() => {
    const fetchTeams = async () => {
      try {
        const response = await fetch('/api/teams');
        const data = await response.json();
        setTeams(data);
      } catch (error) {
        console.error('Error fetching teams:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchTeams();
  }, [refreshTrigger]);

  if (loading) {
    return <div>Loading teams...</div>;
  }

  if (teams.length === 0) {
    return (
      <Card>
        <CardContent className="p-6">
          <div className="text-center text-muted-foreground">
            No teams found. Create your first team to get started.
          </div>
        </CardContent>
      </Card>
    );
  }

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      {teams.map((team) => (
        <Card key={team.id}>
          <CardHeader>
            <CardTitle className="text-lg">{team.name}</CardTitle>
            <div className="flex space-x-2">
              <Badge variant="secondary">{team.sport}</Badge>
              <Badge variant="outline">{team.category}</Badge>
            </div>
          </CardHeader>
          <CardContent className="space-y-2">
            {team.coach_name && (
              <div className="text-sm">
                <span className="font-medium">Coach:</span> {team.coach_name}
              </div>
            )}
            {team.description && (
              <div className="text-sm">
                <span className="font-medium">Description:</span> {team.description}
              </div>
            )}
            <div className="text-sm">
              <span className="font-medium">Created:</span>{' '}
              {new Date(team.created_at).toLocaleDateString()}
            </div>
          </CardContent>
        </Card>
      ))}
    </div>
  );
}
