class User {
  final int id;
  final String name;
  final String email;
  final String phone;
  final String? identityNumber;
  final String? identityType;
  final String? dateOfBirth;
  final String? placeOfBirth;
  final String? gender;
  final String? address;
  final String? photoUrl;
  final bool isVerified;
  final DateTime createdAt;
  final DateTime updatedAt;

  User({
    required this.id,
    required this.name,
    required this.email,
    required this.phone,
    this.identityNumber,
    this.identityType,
    this.dateOfBirth,
    this.placeOfBirth,
    this.gender,
    this.address,
    this.photoUrl,
    // Use email_verified_at to determine verification status
    this.isVerified = false,
    required this.createdAt,
    required this.updatedAt,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    print('USER JSON: $json'); // Debug

    // User is verified if email_verified_at is not null
    final bool isVerified =
        json['email_verified_at'] != null || json['is_verified'] == true;

    return User(
      id: json['id'] ?? 0,
      name: json['name'] ?? '',
      email: json['email'] ?? '',
      phone: json['phone'] ?? '',
      identityNumber: json['id_number'] ?? json['identity_number'],
      identityType: json['id_type'] ?? json['identity_type'],
      dateOfBirth: json['dob'] ?? json['date_of_birth'],
      placeOfBirth: json['place_of_birth'],
      gender: json['gender'],
      address: json['address'],
      photoUrl: json['photo_url'],
      isVerified: isVerified, // Use the determined verification status
      createdAt:
          json['created_at'] != null
              ? DateTime.parse(json['created_at'])
              : DateTime.now(),
      updatedAt:
          json['updated_at'] != null
              ? DateTime.parse(json['updated_at'])
              : DateTime.now(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'phone': phone,
      'identity_number': identityNumber,
      'identity_type': identityType,
      'date_of_birth': dateOfBirth,
      'place_of_birth': placeOfBirth,
      'gender': gender,
      'address': address,
      'photo_url': photoUrl,
      'is_verified': isVerified, // Include verification status
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }

  User copyWith({
    int? id,
    String? name,
    String? email,
    String? phone,
    String? identityNumber,
    String? identityType,
    String? dateOfBirth,
    String? placeOfBirth,
    String? gender,
    String? address,
    String? photoUrl,
    bool? isVerified, // Add to copyWith
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return User(
      id: id ?? this.id,
      name: name ?? this.name,
      email: email ?? this.email,
      phone: phone ?? this.phone,
      identityNumber: identityNumber ?? this.identityNumber,
      identityType: identityType ?? this.identityType,
      dateOfBirth: dateOfBirth ?? this.dateOfBirth,
      placeOfBirth: placeOfBirth ?? this.placeOfBirth,
      gender: gender ?? this.gender,
      address: address ?? this.address,
      photoUrl: photoUrl ?? this.photoUrl,
      isVerified: isVerified ?? this.isVerified,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }
}

class AuthResponse {
  final String accessToken;
  final String refreshToken;
  final String tokenType;
  final int expiresIn;
  final User user;

  AuthResponse({
    required this.accessToken,
    required this.refreshToken,
    required this.tokenType,
    required this.expiresIn,
    required this.user,
  });

  factory AuthResponse.fromJson(Map<String, dynamic> json) {
    // Debug untuk memahami struktur
    print('AUTH RESPONSE JSON: $json');
    // Cek apakah data berada dalam nested data object
    final data = json.containsKey('data') ? json['data'] : json;
    // Cek struktur token
    final String token = data['token'] is String ? data['token'] : '';
    return AuthResponse(
      accessToken: token,
      refreshToken: '', // Laravel tidak mengembalikan refresh token
      tokenType: 'Bearer', // Hardcoded karena Laravel hanya mengembalikan token
      expiresIn: 3600, // Default value karena Laravel tidak spesifik
      user: User.fromJson(data['user']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'access_token': accessToken,
      'refresh_token': refreshToken,
      'token_type': tokenType,
      'expires_in': expiresIn,
      'user': user.toJson(),
    };
  }
}
