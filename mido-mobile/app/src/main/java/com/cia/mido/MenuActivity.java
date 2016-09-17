package com.cia.mido;

import android.content.Intent;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;

public class MenuActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_menu);
    }

    @Override
    public void onBackPressed() {
        finish();
    }

    public void chooseSocialMedia(View view) {
        Intent socialMediaIntent = new Intent(this, SocialMediaActivity.class);
        startActivity(socialMediaIntent);
        finish();
    }
}