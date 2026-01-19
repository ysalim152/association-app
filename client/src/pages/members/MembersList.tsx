import * as React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

interface Member {
  id: number;
  first_name: string;
  last_name: string;
  email: string;
  phone: string | null;
  membership_type: string;
  join_date: string;
  status: string;
}

interface MembersListProps {
  refreshTrigger: number;
}

export function MembersList({ refreshTrigger }: MembersListProps) {
  const [members, setMembers] = React.useState<Member[]>([]);
  const [loading, setLoading] = React.useState(true);

  React.useEffect(() => {
    const fetchMembers = async () => {
      try {
        const response = await fetch('/api/members');
        const data = await response.json();
        setMembers(data);
      } catch (error) {
        console.error('Error fetching members:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchMembers();
  }, [refreshTrigger]);

  if (loading) {
    return <div>Loading members...</div>;
  }

  if (members.length === 0) {
    return (
      <Card>
        <CardContent className="p-6">
          <div className="text-center text-muted-foreground">
            No members found. Add your first member to get started.
          </div>
        </CardContent>
      </Card>
    );
  }

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      {members.map((member) => (
        <Card key={member.id}>
          <CardHeader>
            <CardTitle className="text-lg">
              {member.first_name} {member.last_name}
            </CardTitle>
            <Badge variant={member.status === 'active' ? 'default' : 'secondary'}>
              {member.status}
            </Badge>
          </CardHeader>
          <CardContent className="space-y-2">
            <div className="text-sm">
              <span className="font-medium">Email:</span> {member.email}
            </div>
            {member.phone && (
              <div className="text-sm">
                <span className="font-medium">Phone:</span> {member.phone}
              </div>
            )}
            <div className="text-sm">
              <span className="font-medium">Membership:</span> {member.membership_type}
            </div>
            <div className="text-sm">
              <span className="font-medium">Joined:</span>{' '}
              {new Date(member.join_date).toLocaleDateString()}
            </div>
          </CardContent>
        </Card>
      ))}
    </div>
  );
}
